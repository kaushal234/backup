#!/bin/bash

PHP_BIN='/usr/bin/php'
JOBS_PATH='/var/www/alvest-web-portals/current/admin/jobs'

####################
# Runs these jobs on particular days in the week
####################

case `date +%A` in
  Monday)
    echo It\'s Monday
    # Send late TOC notifications
    $PHP_BIN $JOBS_PATH/weekly/email.toc.notification.php
    # Send late TOC notifications
    $PHP_BIN $JOBS_PATH/weekly/email.customer.toc.notification.php
    ;;
esac

####################
# Runs these jobs on particular days in the actual month
####################

case `date +%d` in
  01)
    echo It\'s the 1st of the month
    # Generate TOC KPI and estimated TP/GT data
    $PHP_BIN $JOBS_PATH/monthly/toc.kpi.php
    $PHP_BIN $JOBS_PATH/monthly/tpgt.graphs.php
    ;;
esac

case `date +%d-%m` in
  31-12)
    echo It\'s the last day of the year
    # Generate TOC KPI and estimated TP/GT data
    $PHP_BIN $JOBS_PATH/other/evendor-report.php 400 'USD'
    $PHP_BIN $JOBS_PATH/other/evendor-report.php 410 'USD'
    $PHP_BIN $JOBS_PATH/other/evendor-report.php 420 'USD'
    $PHP_BIN $JOBS_PATH/other/evendor-report.php 500 'USD'
    $PHP_BIN $JOBS_PATH/other/evendor-report.php 510 'USD'
    $PHP_BIN $JOBS_PATH/other/evendor-report.php 520 'USD'
    $PHP_BIN $JOBS_PATH/other/evendor-report.php 660 'USD'
    $PHP_BIN $JOBS_PATH/other/evendor-report.php 640 'USD'
    $PHP_BIN $JOBS_PATH/other/evendor-report.php 620 'USD'
    ;;
esac
