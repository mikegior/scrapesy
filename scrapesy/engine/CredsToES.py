#!/usr/bin/env python3

# CredsToEs.py
# This is a Python script that is called by 'scrapesy-wd.py' and 'scrapesy-wd-http.py' running as daemons to
# injest, parse, and send relevant credentials scrapped by 'scrapesy.py' modules to a local Elasticsearch instance 
# to be stored and reviewed via WebUI later.

import argparse
import uuid
import logging
import re
from elasticsearch import Elasticsearch
from datetime import datetime
from configparser import ConfigParser

#Define logging parameters
logging.basicConfig(filename="/opt/scrapesy/logs/CredsToES.log",
                    level=logging.DEBUG,
                    format='%(asctime)s - %(levelname)s: %(message)s',
                    datefmt='%Y-%m-%d %H:%M:%S')

class colors:
    WARN = '\033[93m'
    OK = '\033[92m'
    BAD = '\033[91m'
    END = '\033[0m'

def credProcessor(ldapEnabled,gsuiteEnabled,scrapeCriteria,file,es_ip,es_port,es_index):
    #Open combolist file and read each line, use UTF-8 encoding on text coming in, ignore errors
    combolist = open(file, "r", encoding="utf8", errors="ignore")
    line = combolist.readline()

    #Domain and/or explicit email address lookups for combolist
    domains = scrapeCriteria

    #Define Elasticsearch parameters and build connection via 'es' namespace
    es = Elasticsearch([{'host':es_ip, 'port':es_port}])

    #Create document mapping for data to be sent to Elasticsearch
    mapping = {
        "mappings": {
            "properties": {
                "import_time": {
                    "type": "date",
                    "format": "yyyy-MM-dd"
                },
                "username": {
                    "type": "text"
                },
                "password": {
                    "type": "text"
                },
                "source": {
                    "type": "text"
                },
                "is_active": {
                    "type": "text"
                }
            }
        }
    }

    #Check if Elasticsearch index exists; if it does, skip creation and refresh the index; if index does not exist, create it
    es_exists = es.indices.exists(es_index)
    if not(es_exists):
        logging.info(f"'{es_index}' does not exist yet - creating it")
        print(f"{colors.WARN}Index '{es_index}' does not exist yet - creating it{colors.END}")
        es_resp = es.indices.create(index=es_index, body=mapping, ignore=400)
        if "acknowledged" in es_resp:
            if es_resp["acknowledged"] == True:
                logging.info(f"'{es_index}' created successfully on '{es_ip}'")
                print(f"{colors.OK}Index creation & mapping successful: " + es_resp["index"] + f"{colors.END}" + "\n")
            elif "error" in es_resp:
                logging.error(f"Unable to create '{es_index}' on '{es_ip}' - please check Elasticsearch helper errors")
                print(colors.BAD + "Unknown Error:" + es_resp["error"]["root_cause"] + colors.END)
    else:
        logging.info(f"The index '{es_index}' already exists - skipping creation of index")
        print(f"{colors.WARN}Index {es_index} already exists! Refreshing index and skipping...{colors.END}")
        refresh = es.indices.refresh(index=es_index)
        if "_shards" in refresh:
            logging.info(f"Index '{es_index}' was refreshed successfully")
            print(f"{colors.OK}Refresh of {es_index} successful!{colors.END}\n")
        elif "error" in refresh:
            logging.error(f"Unable to refresh '{es_index}' on '{es_ip}' - please check Elasticsearch helper errors")
            print(f"{colors.BAD}Unknown Error:" + refresh["error"] + "{colors.END}\n")

    #Read each line in combolist, parse, and send to Elasticsearch
    while line:
        creds = line.split(":")
        user = creds[0]

        #Sometimes there's no password after the split, in which case we check and if there's nothing, we make it string "NULL"
        try:
            passwd = creds[1]
        except IndexError:
            passwd = "NULL"

        line = combolist.readline()

        #Check for presense of domains/email addresses as defined in domains[] list (by way of config.ini [PARSE_CRITERIA] stanza)
        for domain in domains:
            if domain in user:
                logging.info(f"Leaked credential was found in {file} - will attempt to write them to '{es_index}' on '{es_ip}'")
                print(f"{colors.BAD}[!] Leaked credential found: {user}:{passwd}{colors.END}")

                #Perform account validations if enabled via 'config.ini'
                if gsuiteEnabled == "True":
                    logging.info(f"Checking if {user} is active in GSuite... going to scrapesy_gsuite.py")
                    from scrapesy_gsuite import gsuiteCheck
                    #Check if user is present in GSuite
                    activeUser = gsuiteCheck(user)
                elif ldapEnabled == "True":
                    logging.info(f"Checking if {user} is active in Active Directory... going to scrapesy_ldap.py")
                    #Check if user is present in Active Directory
                    from scrapesy_ldap import ldapCheck
                    activeUser = ldapCheck(user)
                else:
                    #No validation enabled; set 'activeUser' string noting this and continue
                    logging.info(f"No account validation enabled/configured in config.ini, will not validate {user}")
                    activeUser = "Validation Not Configured"

                #Converting account validation status from boolean to string for Elasticsearch
                if activeUser == False:
                    accountActive = "Not Active"
                elif activeUser == True:
                    accountActive = "Active"
                else:
                    accountActive = activeUser

                #Build the payload for values to send to Elasticsearch
                now = datetime.now()
                importTime = now.strftime("%Y-%m-%d")
                data = {
                        "import_time": importTime,
                        "username": user,
                        "password": passwd,
                        "source": file,
                        "is_active": accountActive
                }

                #Write data to Elasticsearch index
                es_write = es.index(index=es_index, id=uuid.uuid4(), body=data)
                if "_index" in es_write:
                    if es_write["_index"] == es_index:
                        logging.info(f"Wrote credential to '{es_index}' on '{es_ip}' successfully")
                        print(colors.OK + "Wrote credentials to ES successfully!\n" + colors.END)
                        print("===================================================================================\n")
                    elif "error" in es_write:
                        logging.error(f"Unable to write credential to '{es_index}' on '{es_ip}' - please check Elasticsearch helper errors")
                        print(colors.BAD + "Unknown Error:", es_write["error"] + colors.END)

if __name__ == "__main__":
    parser = argparse.ArgumentParser()

    parser.add_argument('file', help="Specify filename of combolist to injest")
    parser.add_argument('es_ip', default="localhost", help="Specify the IP address of the Elasticsearch instance")
    parser.add_argument('es_port', default="9200", help="Specify the port of the Elasticsearch instance")
    parser.add_argument('-i', help="Specify the name of the Elasticsearch index to use or create (if new)")

    args = parser.parse_args()

    #Get CredsToES configuration
    config = ConfigParser()
    config.read("/opt/scrapesy/config.ini")

    #Check if LDAP or GSutie account validation is enabled - pass to credProcessor()
    ldapEnabled = config["SCRAPESY_LDAP"]["ENABLED"]
    gsuiteEnabled = config["SCRAPESY_GSUITE"]["ENABLED"]

    #Store scrape criteria into 'scrapeCriteria'
    scrapeCriteria = config["PARSE_CRITERIA"]["CRITERIA"]

    #Read each scrape criteria present; add to scrapeCriteria as list
    #This will be mapped to domains[] in the 'credsProcessor()' function
    for criteria in scrapeCriteria.splitlines():
        scrapeCriteria = [x.strip('"') for x in scrapeCriteria.split(',')]

    #Send values to credProcessor() to start
    credProcessor(ldapEnabled,gsuiteEnabled,scrapeCriteria,args.file,args.es_ip,args.es_port,es_index=args.i)