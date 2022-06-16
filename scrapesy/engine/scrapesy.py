#!/usr/bin/env python3

# scrapesy.py
# This is a Python script that runs on a scheduled basis (cron) and will
# use BeautifulSoup4 to scrape different sources to find and download
# "combolists" that contain leaked/compromised credentials.
#
# This script will download and save the combolist to a local directory
# which the 'scrapesyWatchdog.py' daemon will observe the file creation event
# and call 'CredsToES.py' to review, parse, and send any relevant findings
# to a local Elasticsearch instance.
#
# This script currently scrapes the following using modules:
# - Anonfiles (anonfiles.com) via Google Dorking

import sys, os
import logging
import time
import importlib.util
from configparser import ConfigParser

#Append path for Scrapesy Modules to this script
sys.path.append('/opt/scrapesy/modules')
from anonfiles import anonfilesScraper #Anonfiles.com Scraper Module

#Define logging parameters
logging.basicConfig(filename="/opt/scrapesy/logs/scrapesy.log",
                    level=logging.DEBUG,
                    format='%(asctime)s - %(levelname)s: %(message)s',
                    datefmt='%Y-%m-%d %H:%M:%S')

def configParser():
    config = ConfigParser()
    config.read("/opt/scrapesy/config.ini")

    #Store "ANONFILES_DORK" contents into dorkConfig
    anonfilesQuery = config["SCRAPESY_MODULES"]["ANONFILES_QUERY"]

    #Check Proxy Settings
    proxyEnabled = config["SCRAPESY_PROXY"]["PROXY"]
    proxyAuth = config["SCRAPESY_PROXY"]["PROXY_AUTH"]

    if proxyEnabled == "True":
        proxyHost = config["SCRAPESY_PROXY"]["PROXY_IP"]
        proxyPort = config["SCRAPESY_PROXY"]["PROXY_PORT"]

        proxies = {
            "http": f"http://{proxyHost}:{proxyPort}",
            "https": f"http://{proxyHost}:{proxyPort}"
        }
    elif proxyAuth == "True":
        proxyHost = config["SCRAPESY_PROXY"]["PROXY_IP"]
        proxyPort = config["SCRAPESY_PROXY"]["PROXY_PORT"]
        proxyUser = config["SCRAPESY_PROXY"]["PROXY_USER"]
        proxyPass = config["SCRAPESY_PROXY"]["PROXY_PASS"]

        proxies = {
            "http": f"http://{proxyUser}:{proxyPass}@{proxyHost}:{proxyPort}",
            "https": f"http://{proxyUser}:{proxyPass}@{proxyHost}:{proxyPort}"
        }
    else:
        proxies = { "http": None, "https": None }

    #Read each Google Dork for Anonfiles; add to dorkList[]
    for query in anonfilesQuery.splitlines():
        anonfilesQueries = [x.strip('"') for x in query.split(',')]

    #Call anonfilesScraper() function in anonfiles module
    anonfilesScraper(anonfilesQueries,proxies)

if __name__ == "__main__":
    configParser()