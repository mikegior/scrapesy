#!/usr/bin/env python3
#
# This script is designed to authenticate and authorize with the Scrapesy GCP validation OAuth
# for GSuite-related validations in Scrapesy.
#
# This should be used run on a system with a GUI, so you will be able to accept the authorization
# form from GSuite/GCP presented via a web browser. This will subsequently provide you with your 'token.pickle' 
# file used by Scrapesy to authenticate with the GSuite Admin SDK API.
#
# The 'token.pickle' file will need to be placed into '/opt/scrapesy/credentials/' on your
# Scrapesy server along with your 'credentials.json' file.
#
# Below is example usage of the tool:
#
# python3 scrapesy-gsuite-auth.py
#
# This script will attempt to validate if "null@null.com" exists in your GSuite tenant. The user
# does not need to exist, it will simply attempt to check if it does so a 'token.pickle' file 
# is generated.
#

import os, sys
import pickle
from googleapiclient.discovery import build
from google_auth_oauthlib.flow import InstalledAppFlow
from google.auth.transport.requests import Request
from google.oauth2.credentials import Credentials

def gsuiteCheck(user):
    #Define google sdk/api parameters and oauth2 authentication
    SCOPES = ['https://www.googleapis.com/auth/admin.directory.user.readonly']

    gCreds = None
    if os.path.exists('token.pickle'):
        with open('token.pickle','rb') as token:
            gCreds = pickle.load(token)
    #If there are no (valid) creds available, force the user to login - this can't happen!
    if not gCreds or not gCreds.valid:
        if gCreds and gCreds.expired and gCreds.refresh_token:
            gCreds.refresh(Request())
        else:
            flow = InstalledAppFlow.from_client_secrets_file('credentials.json',SCOPES)
            gCreds = flow.run_local_server(port=0)
        #Save the credentials for the next run
        with open('token.pickle','wb') as token:
            pickle.dump(gCreds,token)
        
    service = build('admin','directory_v1',credentials=gCreds)

    #Validate if test use is present
    try:
        results = service.users().get(userKey=user).execute()
        gUser = results.get('primaryEmail', [])
        if user in gUser:
            print(f"---> {user} was found in Google Workspace Directory\n")
            activeUser = "User Found - token.pickle should be generated now!"
            print(activeUser)
        else:
            print(f"---> {user} was NOT found in Google Workspace Directory\n")
            activeUser = "User Not Found - token.pickle should be generated now!"
            print(activeUser)
    except:
        print(f"---> {user} was not found in Google Workspace Directory\n")
        activeUser = "User Not Found - token.pickle should be generated now!"
        print(activeUser)

if __name__ == "__main__":
    user = "null@null.com"
    gsuiteCheck(user)
