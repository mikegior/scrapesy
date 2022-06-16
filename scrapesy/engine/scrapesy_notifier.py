#!/usr/bin/env python3

# scrapesy_notifier.py
# This is a Python script that will run once per day and check the local
# Scrapesy index (scrapesy-index) in Elasticsearch to determine if there
# have been any new findings since the day prior. If there are new findings,
# this script will send an email notification based on criteria configured
# in the '/opt/scrapesy/config.ini' file's [SCRAPESY_EMAIL] stanza.

import logging
import datetime
import itertools
import smtplib, ssl
from email.message import EmailMessage
from elasticsearch import Elasticsearch
from configparser import ConfigParser

#Define logging parameters
logging.basicConfig(filename="/opt/scrapesy/logs/scrapesy_notifier.log",
                    level=logging.DEBUG,
                    format='%(asctime)s - %(levelname)s: %(message)s',
                    datefmt='%Y-%m-%d %H:%M:%S')

def scrapesyNotifier(sender,password,rcpt,server,port):
    #Define Elasticsearch parameters and build connection via 'es' namespace
    es = Elasticsearch(hosts=["localhost"])

    #Define time parameters
    yesterday = datetime.date.today() - datetime.timedelta(days=1)

    #Setup ES query for all entries from day prior, store in 'query'
    #Feel free to adjust the 'size' value depending on how many results
    #you would like to see in the email notification.
    query = {
      "size": 200,
      "query": {
        "bool": {
          "should": {
            "match": {
              "import_time": yesterday
            }
          }
        }
      }
    }

    #Execute search and store in 'result'
    result = es.search(index="scrapesy-index", body=query)

    #Pull relevant values to build list to send via email
    users = [source['_source']['username'] for source in result['hits']['hits']]
    isActive = [source['_source']['is_active'] for source in result['hits']['hits']]
    dateFound = [source['_source']['import_time'] for source in result['hits']['hits']]

    #Iterate through each user, status, and date; append to tempList[] for now
    tempList = []
    for (user,status,date) in zip(users,isActive,dateFound):
        tempList.append(f"{user} - {date} - {status}" + "\n")

    #Move tempList[] to 'report'
    report = ''.join(map(str,tempList))

    #Setup email authentication, subject, body
    msg = EmailMessage()
    msg['From'] = sender
    msg['To'] = rcpt
    msg['Subject'] = "[ALERT] New findings in Scrapesy!"
    msg_body = f"Hello, Scrapesy has new findings since yesterday!\n\nUSERNAME - DATE FOUND - ACCOUNT STATUS\n{report}"
    msg.set_content(msg_body)

    context = ssl.create_default_context()
    with smtplib.SMTP(server,port) as smtp:
        smtp.ehlo()
        smtp.starttls(context=context)
        smtp.login(sender,password)

        #Send message!
        smtp.send_message(msg)


if __name__ == "__main__":
    #Get Scrapesy configuration
    config = ConfigParser()
    config.read("/opt/scrapesy/config.ini")

    #Check if Email notification is enabled
    emailEnabled = config["SCRAPESY_EMAIL"]["ENABLED"]

    if emailEnabled == "True":
        #Get email settings
        sender = config["SCRAPESY_EMAIL"]["SENDER"]
        password = config["SCRAPESY_EMAIL"]["PASSWORD"]
        rcpt = config["SCRAPESY_EMAIL"]["RCPT"]
        server = config["SCRAPESY_EMAIL"]["SERVER"]
        port = config["SCRAPESY_EMAIL"]["PORT"]

        #Send to scrapesyNotifier()
        scrapesyNotifier(sender,password,rcpt,server,port)
    else:
        #If we're here, email notification is not configured - log it!
        logging.INFO("Email notifications are not configured in config.ini - exiting...")