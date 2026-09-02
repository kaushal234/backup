<?php
include_once('sales_service.inc.php');
include_once('erp.inc.php');

switch ($m[1]) {
	case 'reports':
		include('esr/esr.reports.inc.php');
		break;
}

