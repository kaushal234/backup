#!/bin/bash

PHP_BIN='/usr/bin/php'
# Set where all the job files are
if [ -d /home/www/www.tld-gse.com/admin/jobs ]
then
    JOBS_PATH='/home/www/www.tld-gse.com/admin/jobs'
else
    JOBS_PATH='/var/www/alvest-web-portals/current/admin/jobs'
fi

date

echo Processing JAPAN invoices
$PHP_BIN $JOBS_PATH/hourly/invoices.japan.seq.php
echo

echo Processing outbound ERP messages
$PHP_BIN $JOBS_PATH/hourly/outbound.php
echo


