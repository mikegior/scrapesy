#!/bin/bash
# Scrapesy Install Script
# VERSION: 1.0
#

#Define colors/variables
R='\033[0;31m'
G='\033[0;32m'
N='\033[0m'
IPADDR=`ip -o addr show up primary scope global | awk -d '{print $4}' | cut -f 1 -d /`

cat << "EOF"
  ____                                       
 / ___|  ___ _ __ __ _ _ __   ___  ___ _   _ 
 \___ \ / __| '__/ _` | '_ \ / _ \/ __| | | |
  ___) | (__| | | (_| | |_) |  __/\__ \ |_| |
 |____/ \___|_|  \__,_| .__/ \___||___/\__, |
                      |_|              |___/ 
EOF
echo ""

#Check if we're root
if [ $EUID -ne 0 ]; then
    echo -e "${R}Install is not running as root! Try using 'sudo' to run this script instead.${N}"
    exit 2
fi

#Create Scrapesy install location
echo -e "${G}\n[+] Creating Scrapesy install path (/opt/scrapesy)${N}"
mkdir -p /opt/scrapesy/{logs,combolists,credentials,modules}

#Copy Scrapesy engine files to /opt/scrapesy
echo -e "${G}\n[+] Copying Scrapesy engine to /opt/scrapesy${N}"
cp scrapesy/engine/*.py /opt/scrapesy/
cp scrapesy/engine/config.ini /opt/scrapesy/
cp scrapesy/engine/modules/*.py /opt/scrapesy/modules/
chmod +x /opt/scrapesy/scrapesy.py
chmod +x /opt/scrapesy/scrapesy_notifier.py

#Install pip3
echo -e "${G}\n[+] Installing 'pip3'${N}"
sudo apt -y install python3-pip

#Install Python3 modules via pip3 (see resources/requirements.txt)
echo -e "${G}\n[+] Installing Scrapesy engine (Python) Requirements${N}"
sudo pip3 install -r resources/requirements.txt
sudo pip3 install --upgrade google-api-python-client

#Add systemd script for scrapesyWatchdog daemon
echo -e "${G}\n[+] Creating systemd entries for Scrapesy Watchdog daemons${N}"
sudo cp resources/scrapesy-wd.service /etc/systemd/system/
sudo cp resources/scrapesy-wd-http.service /etc/systemd/system/
sudo systemctl daemon-reload

#Defining mysql root password for unattended installation
echo "mysql-server-8.0 mysql-server/root_password password scrapesy" | sudo debconf-set-selections
echo "mysql-server-8.0 mysql-server/root_password_again password scrapesy" | sudo debconf-set-selections

#Install mysql-server
echo -e "${G}\n[+] Installing MySQL Server${N}"
sudo apt update
sudo apt -y install mysql-server

#Prepare mysql server for Scrapesy database & automate mysql_secure_install
echo -e "${G}\n[+] Preparing MySQL and creating Scrapesy database${N}"
mysql -uroot -pscrapesy < resources/init.sql

#Install elasticsearch
echo -e "${G}\n[+] Installing Elasticsearch${N}"
sudo wget -qO - https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo apt-key add -
sudo sh -c 'echo "deb https://artifacts.elastic.co/packages/7.x/apt stable main" > /etc/apt/sources.list.d/elastic-7.x.list'
sudo apt update
sudo apt -y install elasticsearch

#Install composer and related packages
echo -e "${G}\n[+] Installing composer and related packages${N}"
sudo apt -y install php-cli php-mbstring unzip
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

#Install apache2 and required dependencies
echo -e "${G}\n[+] Installing Apache2, PHP, and related PHP/Apache2 modules${N}"
sudo apt -y install apache2 php libapache2-mod-php php-mysql php-curl

#Move webapp source to /var/www/html
echo -e "${G}\n[+] Copying Scrapesy webapp files to Apache2 docroot (/var/www/html)${N}"
sudo rm -f /var/www/html/index.html
sudo cp -a webapp/src/. /var/www/html

#Installing elasticsearch helper via Composer
echo -e "${G}\n[+] Installing Elasticsearch helper via Composer${N}"
sudo -u $USER bash -c 'composer require elasticsearch/elasticsearch:7.10.0'
sudo cp -ar *.json *.lock vendor/ /var/www/html

#Change ownership of all WWW assets to www-data
echo -e "${G}\n[+] Performing 'chown' of all web assets to www-data${N}"
sudo chown -R www-data:www-data /var/www/html/

#Wrap-up installation; start scrapesyWatchdog daemon, setup cronjob
echo -e "${G}\n[+] Starting Scrapesy Watchdog, Apache2, and Elasticsearch services - this may take several minutes!${N}"
sudo systemctl daemon-reload
sudo systemctl start scrapesy-wd.service
sudo systemctl enable scrapesy-wd.service
sudo systemctl start scrapesy-wd-http.service
sudo systemctl enable scrapesy-wd-http.service
sudo systemctl start apache2.service
sudo systemctl enable apache2.service
sudo systemctl start elasticsearch.service
sudo systemctl enable elasticsearch.service

#Create cron job for scrapesy.py
echo -e "${G}\n[+] Creating cron job for Scrapesy and Notifier (Scrapesy will run once per day at 6AM local time, Notifier at 9AM local time)${N}"
echo "0 6 * * * /usr/bin/python3 /opt/scrapesy/scrapesy.py >/dev/null 2>&1" >> /var/spool/cron/crontabs/root
echo "0 9 * * * /usr/bin/python3 /opt/scrapesy/scrapesy_notifier.py >/dev/null 2>&1" >> /var/spool/cron/crontabs/root

echo -e "${G}\n\n[+] INSTALLATION OF SCRAPESY IS COMPLETE!${N}"
echo -e "${R}=> The default login for Scrapesy is 'admin:changeme' which can, and should, be changed after logging in!${N}"
echo -e "${R}=> The root account of the MySQL instance has a default password of 'scrapesy' - you may change this, but will need to reflect that change in the '/var/www/html/config.php' file!${N}"
echo -e "${G}=> Browse to http://${IPADDR}/ and login to start using Scrapesy!${N}"
echo -e "${G}=> Don't forget to setup your scrape and parse criteria in the config.ini file!${N}"
echo -e "${G}=> Refer to the documentation on GitHub for post-installation task information.${N}"