#!/bin/bash

echo "BEGIN TEMP CLEANING"

echo "Cleaning manuals..."
find /var/www/cache/manuals/ -name "manual*.zip" -ctime +7 -exec rm -f {} \;

echo "Cleaning temp files..."
find /tmp -type f -regextype "posix-extended" -iregex '.*\.(zip|pdf|jpg|png|html)$' -atime +1 -exec rm -f {} +

echo "Cleaning html2pdf & pdftk temp files"
rm -f /tmp/html2pdf*
rm -f /tmp/pdftk*

echo "END TEMP CLEANING"