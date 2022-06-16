![Scrapesy](https://github.com/mikegior/scrapesy/blob/main/images/scrapesy_blacktext_white_bg.png?raw=true)\
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Black Hat Arsenal](https://raw.githubusercontent.com/toolswatch/badges/master/arsenal/usa/2021.svg?sanitize=true)](https://www.toolswatch.org/blackhat-arsenal-us-2021-archive/)

## What is Scrapesy?
Scrapesy is a credential scraping and validation tool.

Scrapesy will scrape, download, ingest, and parse combolists from known web sources and checks them against a list of domains related to your organization for potentially compromised accounts. Additionally, Scrapesy can explicitly monitor for email addresses of interest to determine if any passwords related to users within the organization, or associated with your organizations, have had their accounts/passwords compromised at any point.

Scrapesy can validate discovered users against internal sources of truth, such as Active Directory or Google Workspace (GSuite), to determine if discovered email addresses and accounts are active within the organization. This can help initiate threat hunting activity.

## Installing Scrapesy
While Scrapesy was developed in Python and PHP, the installation script is built for Debian-based Linux distributions and has been tested extensively on Debian 10 and Ubuntu 20.04 installations. Since Scrapesy relies on software that is available on other Linux distributions, it can be installed on other distributions with modifications to the installation script.

To install Scrapesy, perform the following steps:

```bash
git clone https://github.com/mikegior/scrapesy.git
cd scrapesy/
chmod +x install.sh
sudo ./install.sh
```

The install script will automatically download and install software, dependencies, and copy the core Scrapesy files. The core software includes:

- Python3 (and required packages)
- Apache2
- PHP
- MySQL
- Elasticsearch
- Composer

After installation, you will be able to browse to the Scrapesy WebUI and login with the default credentials **which you should change upon login!**

Default username/password: `admin:changeme`

The install script sets the MySQL root account password to `scrapesy` by default, but you can change this later. If you do, ensure you reflect that change in the following file: `/var/www/html/config.php` 

```php
define('DB_PASSWORD', '<YOUR_NEW_PASSWORD>');
$DB_PASSWORD('<YOUR_NEW_PASSWORD');`
```

## Using Scrapesy
By default, Scrapesy will scrape sources once per day at 6AM local time via a cron job.

You can modify the Google Dork's used by Scrapesy's modules by modifying the appropriate field under the `[SCRAPESY_MODULES]` stanza within the `config.ini` file as needed.

The command will return any credentials discovered based on the parsing criteria configured within `CredsToES.py` per the `domains[]` list, which is configured per the `[SCRAPE_CRITERIA]` stanza within the `config.ini` file. See the **Configuring Scrape Sources and Parse Criteria** section for more information on modifying scrape sources and parse criteria.

You can also use the `CredsToES.py` script to manually submit credential leaks/dumps for review by issuing the following command:

`sudo python3 /opt/scrapesy/CredsToES.py /path/to/creds.txt localhost 9200 -i scrapesy-index`

This assumes you've uploaded your credentials file to the Scrapesy server.

**Example output:**
```
Index scrapesy-index already exists! Refreshing index and skipping...
Refresh of scrapesy-index successful!

[!] Leaked credential found: john.smith@example.com:S3cureP@ss1

Wrote credentials to ES successfully!

===================================================================================

[!] Leaked credential found: din.djarin@example2.com:ThisIsTheW@y!

Wrote credentials to ES successfully!

===================================================================================

[!] Leaked credential found: user@example3.com:Summer2019!

Wrote credentials to ES successfully!

===================================================================================
```

Any relevant credentials discovered as configured in `CredsToES.py` will be parsed and sent to Elasticsearch for review via the Scrapesy WebUI.

Scrapesy's Elasticsearch index is `scrapesy-index` by default. Sending credentials to a different index when using the above command will create the index and send relevant findings, but these will not be observable in the Scrapesy WebUI.

**Example Scrapesy WebUI search**
![image](https://github.com/mikegior/scrapesy/blob/main/images/scrapesy_webui.png?raw=true)

Within Scrapesy, you can use the "Help" page to learn more about how to leverage the search functionality such as explicit searches or wildcard searches.

## Configuring Scrape Sources and Parse Criteria
First, you should modify the `config.ini` file in the Scrapesy install directory and set criteria for specifics (i.e. domains, email addresses, and/or usernames to parse for) You can add as much criteria as you like, just ensure they are wrapped in quotes and comma-separated.

```bash
[SCRAPE_CRITERIA]
CRITERIA = "@mydomain.com","vip@mydomain.com","myotherdomain.com"
```

You should also specify queries for sources to monitor for your specific domains and/or explicit email addresses of interest. You can add as many as you like, just ensure they are wrapped in quotes and comma-separated. These are effectively Google Dorks used by the (first) built-in module for Anonfiles.

```bash
[SCRAPESY_MODULES]
ANONFILES_ENABLED = True
ANONFILES_QUERY = "site:cdn-*.anonfiles.com @example.com","site:cdn-*.anonfiles.com @example2.com"
```

This will tell Scrapesy: what to look for and what to parse for when found.

## Configuring Proxy Support for Scrapesy
You can enable proxy support (with or without authentication) by modifying the `[SCRAPESY_PROXY]` stanza in the `config.ini` file.

Change the `PROXY` value to `True` to enable proxy support and provide the relevant IP address or hostname and port. If your proxy requires authentication, change the `PROXY_AUTH` value to `True` and provide the relevant username and password.

```bash
#If a proxy is required, change 'PROXY' value to "True"
[SCRAPESY_PROXY]
PROXY = False
PROXY_IP = 1.2.3.4
PROXY_PORT = 1234

#If proxy authentication is required change 'PROXY_AUTH' to "True"
PROXY_AUTH = False
PROXY_USER = username
PROXY_PASS = password
```

When `PROXY` is set to `False` Scrapesy will pass HTTP and HTTPS proxy values as `None`

## Enabling Email Alerts in Scrapesy
If new findings are discovered by Scrapesy, you can enable email notifications that will alert you once per day at 9AM local time. The email notification includes:

- Username/Email Address Discovered
- Date Discovered
- Validation Status (if validations are enabled/configured)

You can enable email notifications by modifying the `[SCRAPESY_EMAIL]` stanza in the `config.ini` file. Below is an example of the cofiguration.

```bash
#Email alert settings
[SCRAPESY_EMAIL]
ENABLED = False
SENDER = scrapesy@example.com
PASSWORD = P@ssW0rd!
RCPT = user@exmaple.com,distribution_list@example.com
SERVER = <IP or HOST>
PORT = 587
```

## Enabling & Configuring Account Validation (LDAP, GSuite)
Scrapesy has the ability to take email addresses/accounts discovered and validate them against a source of truth - such as, Active Directory or GSuite.

Scrapesy leverages LDAP to check against your Active Directory to determine, via the LDAP `email` attribute, if an account is present and active in your environment. Similarly for GSuite, Scrapesy leverages the `admin.directory.user.readonly` scope via Workplace Admin SDK to validate if a user is present and active in your GSuite tennant.

Using GSuite as a validation method does require a GSuite instance as well as having a Google Cloud Platform (GCP) instance as well, refer to Scrapesy's full documentation to configure GSuite/GCP for account validations.

You can enable account validation via the `config.ini` file by changing the `ENABLED` values to `True` under the respective stanza. Note: only one validation method can be enabled at a time!

When using LDAP, configuring additional parameters such as your Active Directory host, LDAP bind username, LDAP bind password, and the LDAP search base is required. Example values are provided in the `config.ini` file for reference.

```bash
#To validate user accounts in Active Directory (via LDAP) set 'ENABLED' to 'True' and provide host, user, pass, and base DN
[SCRAPESY_LDAP]
ENABLED = False
LDAP_HOST = 1.2.3.4
LDAP_BIND_USER = CN=svc_scrapesy,OU=Users,DC=domain,DC=local
LDAP_BIND_PASS = P@ssW0rd!
LDAP_BASE = DC=domain,DC=local

#To validate user accounts in GSuite Workspace Directory (via API) set 'ENABLED' to 'True' and ensure 'credentials.json' and 'token.pickle' are in the '/opt/scrapesy/credentials' directory - see scripts/gsuite/README.md for more information on configuring GSuite
[SCRAPESY_GSUITE]
ENABLED = False
```

## Upcoming Feature Enhancements
The following is a list of enhancements that are currently being developed to add into Scrapesy's functionality:

- [ ] SIEM Integrations
- [ ] Dockerize!
- [ ] Update install script to accomodate RHEL-based distributions (i.e.: Red Hat, CentOS)
- [x] Email Alerting
- [x] Configuration file support (i.e. change sources, scrape criteria, settings, etc.)
- [x] Proxy Support
- [x] Export Results to CSV
- [x] Upload credential dump/leak via WebUI
