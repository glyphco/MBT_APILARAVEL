#!/bin/bash

#--Select the code root - This is the only variable that should be changed--#
root=/mbt/mbtapi/


#Log Location
logfile=$root/storage/logs/aro-ComposerUpdate.log



echo '********************'$(date -u)' - Deploy Process BEGIN ********************' >> $logfile 2>&1

#---- 1/4 - Start Composer self update ----#
cd /usr/local/bin/ >> $logfile 2>&1
sudo composer self-update >> $logfile 2>&1
echo '---Composer Self-Update Complete---' >> $logfile 2>&1

#---- 2/4 - Start Pre-Composer update ----#
cd $root >> $logfile 2>&1
sudo chmod -R 777 storage
sudo chmod -R 777 bootstrap/cache
php artisan config:cache
echo '---Pre Composer Update Complete---' >> $logfile 2>&1

#---- 2/4 - Start Composer update ----#
sudo composer install --optimize-autoloader >> $logfile 2>&1
echo '---Composer Update Complete---' >> $logfile 2>&1

#---- 3/4 - Start Laravel Optimization ----#
php artisan route:clear
php artisan optimize --force
php artisan route:cache
php artisan config:cache
echo '---Optimized---' >> $logfile 2>&1

#---- 4/4 - Start Database destroy and rebuild
php artisan droptables  >> $logfile 2>&1
php artisan migrate --force >> $logfile 2>&1
php artisan db:seed >> $logfile 2>&1

echo '---Database Migrated---' >> $logfile 2>&1



echo '********************'$(date -u)' - Deploy Process COMPLETE ********************' >> $logfile 2>&1

