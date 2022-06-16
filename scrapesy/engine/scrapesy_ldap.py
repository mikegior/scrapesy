#!/usr/bin/env python3

# scrapesy_ldap.py
# This is a Python script that is imported via CredsToES.py to validate
# if an account is present in Active Directory (via LDAP).
#
# This will only function if LDAP is configured and if the config.ini
# file has been configured to set '[SCRAPESY_LDAP]' 'ENABLE' value to 'True'
# and relevant configurations are provided (username, password, LDAP Base DN, etc.)

import logging
from configparser import ConfigParser
from ldap3 import Server, Connection, ALL

#Define logging parameters
logging.basicConfig(filename="/opt/scrapesy/logs/scrapesy_ldap.log",
                    level=logging.DEBUG,
                    format="%(asctime)s - %(levelname)s: %(message)s",
                    datefmt="%Y-%m-%d %H:%M:%S")

class colors:
    WARN = '\033[93m'
    OK = '\033[92m'
    BAD = '\033[91m'
    END = '\033[0m'

def ldapCheck(user):
    #Get CredsToES configuration from 'config.ini'
    config = ConfigParser()
    config.read("/opt/scrapesy/config.ini")

    #Read config.ini file for LDAP values
    ldapServer = config["SCRAPESY_LDAP"]["LDAP_HOST"]
    ldapBindUser = config["SCRAPESY_LDAP"]["LDAP_BIND_USER"]
    ldapBindPass = config["SCRAPESY_LDAP"]["LDAP_BIND_PASS"]
    ldapBase = config["SCRAPESY_LDAP"]["LDAP_BASE"]
    ldapAttribute = "mail"

    #Define ldapSearchFilter and build LDAP connection
    ldapSearchFilter = f"(&(objectClass=user)(mail={user}))"
    ldapConn = Connection(ldapServer, ldapBindUser, ldapBindPass, auto_bind=True)
    ldapConn.search(search_base=ldapBase, search_filter=ldapSearchFilter, search_scope='SUBTREE', attributes=[ldapAttribute])

    #Check each user against LDAP, if user is found set 'activeUser' accordingly and return
    for entries in ldapConn.entries:
        attributes = entries.entry_attributes_as_dict
        email = attributes.get("mail")
        if email:
            newLdapEmails = ",".join(email)
            if newLdapEmails in user:
                logging.info(f"{user} was found in Active Directory")
                print(f"{colors.BAD} ---> {user} was found in Active Directory{colors.END}\n")
                activeUser = True
                return activeUser

    #If user is not found via LDAP search, set 'activeUser' accordingly and return
    logging.error(f"{user} was NOT found in Active Directory")
    print(f"{colors.OK} ---> {user} was NOT found in Active Directory{colors.END}\n")
    activeUser = "User Not Found"
    return activeUser