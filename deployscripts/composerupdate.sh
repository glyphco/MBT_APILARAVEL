#!/bin/bash

#--Select the code root - This is the only variable that should be changed--#
root=/mbt/mbtapi/


#Log Location
logfile=$root/storage/logs/aro-ComposerUpdate.log



echo '********************'$(date -u)' - Start COMPOSER UPDATE Process ********************' >> $logfile 2>&1

#---- 1/2 - Start Composer self update----#
cd /usr/local/bin/ >> $logfile 2>&1
sudo composer self-update >> $logfile 2>&1
echo '---Self-Update Composer Complete---' >> $logfile 2>&1


#---- 2/2 - Start Composer update----#
cd $root >> $logfile 2>&1
sudo composer update >> $logfile 2>&1
php artisan migrate:refresh  >> $logfile 2>&1
php artisan db:seed >> $logfile 2>&1
echo '---Composer Update Complete---' >> $logfile 2>&1

echo '********************'$(date -u)' - COMPLETE COMPOSER UPDATE Process ********************' >> $logfile 2>&1
