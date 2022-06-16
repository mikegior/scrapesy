#!/usr/bin/env python3

# scrapesy_gsuite.py
# This is a Python script that is imported via CredsToES.py to validate
# if an account is present in GSuite.
#
# This will only function if GCP/GSuite is configured and if the config.ini
# file has been configured to set '[SCRAPESY_GSUITE]' 'ENABLE' value to 'True'
#
# Additionally; you will need to copy the 'credentials.json' file provided via GCP
# and the 'token.pickle' file into the '/opt/scrapesy/credentials' directory.
#
# REFERENCE: https://developers.google.com/admin-sdk/directory/v1/quickstart/python

import os
import pickle
import logging
from googleapiclient.discovery import build
from google_auth_oauthlib.flow import InstalledAppFlow
from google.auth.transport.requests import Request
from google.oauth2.credentials import Credentials

#Define logging parameters
logging.basicConfig(filename="/opt/scrapesy/logs/scrapesy_gsuite.log",
                    level=logging.DEBUG,
                    format="%(asctime)s - %(levelname)s: %(message)s",
                    datefmt="%Y-%m-%d %H:%M:%S")

class colors:
    WARN = '\033[93m'
    OK = '\033[92m'
    BAD = '\033[91m'
    END = '\033[0m'

def gsuiteCheck(user):
    #Define Google Directory SDK/API parameters and OAuth2 authentication
    SCOPES = ['https://www.googleapis.com/auth/admin.directory.user.readonly']
                
    gCreds = None
    if os.path.exists('/opt/scrapesy/credentials/token.pickle'):
        with open('/opt/scrapesy/credentials/token.pickle','rb') as token:
            gCreds = pickle.load(token)
    #If there are no (valid) creds available, force the user to login - this can't happen!
    if not gCreds or not gCreds.valid:
        if gCreds and gCreds.expired and gCreds.refresh_token:
            gCreds.refresh(Request())
        else:
            flow = InstalledAppFlow.from_client_secrets_file('/opt/scrapesy/credentials/credentials.json',SCOPES)
            gCreds = flow.run_local_server(port=0)
        #save the credentials for the next run
        with open('token.pickle','wb') as token:
            pickle.dump(gCreds,token)
        
    service = build('admin','directory_v1',credentials=gCreds)
    
    #Validate if users are present
    try:
        results = service.users().get(userKey=user).execute()
        gUser = results.get('primaryEmail', [])
        if user in gUser:
            logging.info(f"{user} was found in Google Workspace Directory")
            print(f"{colors.BAD} ---> {user} was found in Google Workspace Directory{colors.END}\n")
            activeUser = True
            return activeUser
        else:
            logging.error(f"{user} was NOT found in Google Workspace Directory")
            print(f"{colors.OK} ---> {user} was NOT found in Google Workspace Directory{colors.END}\n")
            activeUser = "User Not Found"
            return activeUser
    except:
        logging.error(f"{user} was not found in Google Workspace Directory or unknown error occured")
        print(f"{colors.OK} ---> {user} was not found in Google Workspace Directory{colors.END}\n")
        activeUser = "User Not Found"
        return activeUser 
