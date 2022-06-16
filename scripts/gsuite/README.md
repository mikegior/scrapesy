**Please note, you must run this helper script on a system with a desktop environment and a web browser.**

If you are configuring Scrapesy to use GSuite validation, Scrapesy will call the `scrapesy_gsuite.py` script to validate findings while running. This script requires a valid `credentials.json` and `token.pickle` file to be placed inside the `/opt/scrapesy/credentials/` directory in order for validation using GSuite to work.

You can generate the required `token.pickle` file by using the helper script in this folder.

**You should already have your `credentials.json` file after following the steps in the documentation to configure the Workplace Admin SDK, which must be placed in this folder before continuing.** If you have not set up the Workplace Admin SDK, please refer to the documentation on GitHub.

## Install Python Modules for Helper Script
This folder contains a `requirements.txt` file for Google Python SDK and related modules required by the helper script.

To install the required modules, issue the following command:

`pip3 install -r requirements.txt`

## Generate the `token.pickle` File
Next, execute the helper script to generate the required `token.pickle` file by issuing the following command:

`python3 scrapesy-gsuite-auth.py`

This will automatically open a Google OAuth page in your web browser. When presented with this, simply click "Allow Access"

The purpose of this helper script is to *attempt* to validate a user in GSuite so a `token.pickle` file is generated.

## What's next?
After running the helper script and permitting access via the prompt in your web browser, you can transfer both the `credentials.json` and `token.pickle` files to the `/opt/scrapesy/credentials/` directory on your Scrapesy server.

Once both files are on the Scrapesy server, simply edit the `config.ini` file and set the `ENABLED` value to `True` under the `[SCRAPESY_GSUITE]` stanza.