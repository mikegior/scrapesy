#!/usr/bin/env python3

# anonfiles.py
# This Python script is a Scrapesy Module desgined to scrape via Google Dorking
# the Anonfiles CDN for results matching the value(s) of the "ANONFILES_QUERY" setting
# under the "[SCRAPESY_MODULES]" stanza in the config.ini file.

import sys, os
import logging
import re
import requests
import urllib
import json
import time
from bs4 import BeautifulSoup

#Define logging parameters
logging.basicConfig(filename="/opt/scrapesy/logs/anonfiles_module.log",
                    level=logging.DEBUG,
                    format='%(asctime)s - %(levelname)s: %(message)s',
                    datefmt='%Y-%m-%d %H:%M:%S')

def anonfilesScraper(anonfilesQueries,proxies):
    dorks = anonfilesQueries

    for dork in dorks:
        url = f"https://www.google.com/search?&q={dork}"
        
        #Search and Scrape
        session = requests.Session()
        session.proxies = proxies
        googleDorkReq = session.get(url)
        googleDorkScrape = BeautifulSoup(googleDorkReq.content,features="html.parser")

        for link in googleDorkScrape.findAll("a",href=re.compile("(?<=/url\?q=)(htt.*://.*)")):
            #Normalize all URL's found via "<a href" tag in googleDorkScrape
            googleLinks = re.split(":(?=http)",link["href"].replace("/url?q=",""))
            googleLinksClean = ''.join(googleLinks) #Convert URL's from list to a string

            #Regex to grab the Anonfiles file id from googleLinks
            regex = r"\/([a-zA-Z0-9]{10})\/" 
            anonfileFileId = re.findall(regex,googleLinksClean)
            anonfileFileIdClean = ''.join(anonfileFileId) #Convert file id from a list to a string

            #Check if any of the scraped URL's are not anonfiles.com; if so, skip it
            if "google.com" in googleLinksClean:
                print("A link that is not anonfiles.com was scaped - skipping\n")
                logging.error(f"A link that was non anonfiles.com was scrapped {googleLinksClean} - skipping")
                print("==============================\n")
                continue
            elif not anonfileFileIdClean:
                print("Likely got an anonfiles.com link that wasn't a download link - skipping\n")
                logging.error(f"An anonfiles.com link that wasn't a download link was scrapped {googleLinksClean} - skipping")
                print("==============================\n")
                continue
            else:
                print(f"Scapped link is: {googleLinksClean}\n")
                print("Anonfiles file id is: " + anonfileFileIdClean + "\n")
                logging.info("Anonfiles file id is: " + anonfileFileIdClean)

            #Use Anonfiles API to do some validation of the scrapped Google Dork links
            apiReq = f"https://api.anonfiles.com/v2/file/{anonfileFileIdClean}/info"
            print(f"Anonfiles API URL will be: {apiReq}\n")
            logging.info(f"Requesting download info via anonfiles api - url: {apiReq}")
            anonfileApi = requests.get(apiReq)
        
            #Error Handling - check headers for 'application/json'; if valid, doublecheck that it's not an error; if no JSON at all, it's a 404
            apiJsonCheck_header = anonfileApi.headers['Content-Type']
            apiJsonCheck_content = anonfileApi.json()
            if "application/json" in apiJsonCheck_header:
                if "status" in apiJsonCheck_content:
                    if apiJsonCheck_content["status"] == True:
                        print("Got JSON response from API... checking if file is available for download\n")
                        logging.info(f"Anonfiles file id '{anonfileFileIdClean}' requested via '{apiReq}' and was identified as available - will try to download")
                    elif apiJsonCheck_content["status"] == False:
                        print("JSON response recieved, but it appears the file is no longer available for download - skipping!")
                        print("\n==============================\n")
                        logging.error(f"Anonfiles file id '{anonfileFileIdClean}' is no longer available for download - skipping")
                        continue
            else:
                print("No JSON response recieved at all, likely 404 - skipping!")
                print("\n==============================\n")
                logging.error(f"Anonfiles file id {anonfileFileIdClean} was requested via {apiReq} and JSON response was not recieved, likely 404 - skipping!")
                continue
        
            #Get JSON response & get full URL, filename elements
            jsonData = anonfileApi.json()
            txtFileElement = jsonData["data"]["file"]["url"]["full"]
            txtFileFilename = jsonData["data"]["file"]["metadata"]["name"]
            anonfileDownloadUrl = requests.get(txtFileElement)

            #anonfiles CDN availability can be touchy - doublecheck that the main download page for the file isn't unavailable (HTTP 503)
            if anonfileDownloadUrl.status_code == 503:
                print("We recieved a JSON response earlier, but the download page is giving a 503 now - will try again on the next run!")
                print("\n==============================\n")
                logging.error(f"We recieved a JSON response earlier via {apiReq} for file id {anonfileFileIdClean} but getting a 504 now - will try again on the next run!")
                continue
            
            #Scrape anonfiles download page of file id for final direct download URL
            anonfileScrape = BeautifulSoup(anonfileDownloadUrl.content,features="html.parser")

            #Only consider findings that are TXT files (\.txt) Regex
            for anonfile in anonfileScrape.findAll("a",href=re.compile("(htt.*://.*)(\.txt)")):
                directTxtDownload = re.split(":(?=http)",anonfile["href"].replace("/url?q=",""))
                directTxtDownloadClean = ''.join(directTxtDownload)
                directTxtDownloadClean.strip('[]')
                print("Direct download URL confirmed: " + directTxtDownloadClean)
                logging.info(f"Direct URL for download for anonfiles id '{anonfileFileIdClean}' is '{directTxtDownloadClean}' - passing to wget to download.")
        
                #Call os.system() to use 'wget' to store download to 'combolists/' directory
                #Sleep for 10 seconds
                #Replace any spaces in combolist filename with '_' to avoid headaches later
                #wget will skip already existing files, try to download 2 times, and will timeout after 60 seconds
                time.sleep(10)
                txtFileFilename.replace(" ","_")
                os.system(f'cd /opt/scrapesy/combolists && wget --tries 2 --timeout 60 --no-clobber "{directTxtDownloadClean}" -O {txtFileFilename}')
                print("\n==============================\n")