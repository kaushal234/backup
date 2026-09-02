<html>
<head>
    <title>Newsletter</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#FFFFFF">
<?php
//increase execution time for this script
ini_set('max_execution_time', 0);

include_once 'common.inc.php';
include_once 'Mail.php';
include_once 'Mail/mime.php';
include_once 'forms_and_reports.inc.php';
include_once 'sales_service.inc.php';

$_CSS = <<<EOF
<style type="text/css">
	BODY				{
		font-family:arial,helvetica;
		margin-left:0;
		margin-top:0;
		margin-right: 0px;
		margin-bottom: 0px;
	}
	TD 					{ font-family:arial, helvetica; ; vertical-align: top; font-size: x-small}
	A 					{ color:#3264c8; text-decoration:none }
	A:hover{ color:#FF0000; text-decoration:underline}
	.spec 				{ border : 1px solid #9F9F9F; }
	TD.spectitle		{ border-bottom : 1px solid #9F9F9F; }
	TD.speccontent		{ border-left : 1px solid #9F9F9F; border-bottom : 1px solid #9F9F9F; }
	.smalltext {  font-family: Arial, Helvetica, sans-serif; font-size: x-small}
	.smallwhite {  font-family: Arial, Helvetica, sans-serif; color: #FFFFFF; background-color: #3264C8}
	.xsmalltext { font-family: Arial, Helvetica, sans-serif; font-size: xx-small}
	.xsmalltext { font-family: Arial, Helvetica, sans-serif; font-size: xx-small}
	.xxsmalltext { font-family: Arial, Helvetica, sans-serif; font-size: 9px}
	h4 {  font-family: Arial, Helvetica, sans-serif; font-size: medium; font-style: italic; font-weight: bold; color: #666666}
	.table_title {  background-image: url(/shared/backgrounds/borders/navbg_grey.gif); text-align: center}
	a:visited {  text-decoration: none; border-style: none}
	ul {  list-style-position: outside; list-style-image: url(shared/bullets/Circles/circle03_blue.gif); font-size: x-small}
	img {  border-style: none}
	p {  font-family: Arial, Helvetica, sans-serif; font-size: x-small}
	.alert {  font-style: italic; color: #FF0000}
	tr {  vertical-align: top}
	h2 {  padding-bottom: 0px; margin-bottom: 0px; color: #666666}
	.pagebreak {  page-break-before: Always}
	ol {  font-size: x-small}
	h3 {  color: #666666}
	.mainbodyfont {
		font-size: xx-small;
	}
	th {
		font-size: x-small;
		text-align: left;

	}
</style>
EOF;
//Newsletter emailing function
//Emails on a per day basis
//Operates when $email_newsletter is set
?>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    <input type="hidden" name="email_newsletter" value="1">
    Date of news to send (YYYY-MM-DD): leave blank to send <?php echo date('Y-m-d') ?> news.<br><input type="text"
                                                                                                       name="setdate"><br>
    Check for test run: <input type="checkbox" name="test" value="yes" checked><br>
    <input type="submit" name="submit" value="send">
    <input type="reset" name="reset" value="reset">
</form>

<?php
$cli = $argv[1] && $argv[1] === '--batch';

$logFormatter = function ($message) use ($cli) {
    echo sprintf('%s%s%s', $cli === true ? '[' . date('Y-m-d h:i:s') . '] ' : '', $message, $cli === true ? "\n" : '<br>');
};

if (isset($email_newsletter) || $cli === true) {
    if (empty($setdate)) {
        $setdate = date('Y-m-d');
    }

    if (!checkdate(substr($setdate, 5, 2), substr($setdate, 8, 2), substr($setdate, 0, 4))) {
        echo "Date '$setdate' is not formatted correctly: ";
        exit;
    }
    //Calculate day of week, zero=SUNDAY
    $dayofweek = date('w', mktime(0, 0, 0, substr($setdate, 5, 2), substr($setdate, 8, 2), substr($setdate, 0, 4)));

    $general_toc = <<<EOF
	<p><b>General</b></p>
	<ul>
	<li><a href="#news">News</a></li>
	<li><a href="#holidays">Holidays</a></li>
	</ul>
	<p>
EOF;
    $service_toc = <<<EOF
	<p><b>Service</b></p>
	<ul>
 	<li><a href="#shipments">Shipments</a></li>
 	<li><a href="#warranties">Warranties</a></li>
 	<li><a href="#bulletins">Service Bulletins</a></li>
 	<li><a href="#service_records">Service Records</a></li>
	</ul>
	<p>
EOF;


    //create news list for today
    if ($dayofweek == 0) {
        $news_query = <<<EOF
		SELECT * FROM internal_news
		WHERE date between SUBDATE('$setdate',INTERVAL 1 DAY) and '$setdate' ORDER BY id ASC
EOF;
    } else {
        $news_query = "SELECT * FROM internal_news WHERE date='$setdate' ORDER BY id ASC";
    }
    $rows = tldUtils::getSqlToAssocArray($news_query);

    if ($error = TldDatabase::error()) {
        $logFormatter($error);
    }

    if (empty($rows)) {
        $news_list .= "No news articles to send for $setdate<p>";
    } else {
        //create message body
        $news_list .= <<<EOF
		<p><b><a name="news"></a>TLD Daily News</b><p>
EOF;
        foreach ($rows as $article) {
            $news_list .= '<li><a href="http://www.tld-gse.com/en/private/internal_news/internal_news.php?mode=record_view&form_type=main_tpl&id=' .
                $article['id'] . '">' . stripslashes($article['title']) . "</a></li>\n";
        }
    }

    //create holiday list for today
    $news_list .= '<p><b><a name="holidays"></a>Holidays within next 30 days</b><p>';
    $query = "SELECT * FROM cal_events WHERE date BETWEEN '$setdate' AND DATE_ADD('$setdate',INTERVAL 30 DAY) ORDER BY date";

    $result = tldUtils::getSqlToAssocArray($query);
    if (empty($result)) {
        $news_list .= "No holidays to send between $setdate and +30 days<p>";
    } else {
        $news_list .= '<table border="1">';
        $news_list .= '<tr><td><font size="-1">Company</font></td><td><font size="-1">Date</font></td><td><font size="-1">Holiday</font></td></tr>';
        $companies = tldUtils::getSqlToAssocArray('SELECT DISTINCT company FROM cal_events WHERE date BETWEEN curdate() AND DATE_ADD(curdate(),INTERVAL 30 DAY) ORDER BY company');

        foreach ($companies as $company) {
            $result = tldUtils::getSqlToAssocArray("SELECT * FROM cal_events WHERE company='" . $company['company'] . "' AND date BETWEEN curdate() AND DATE_ADD(curdate(),INTERVAL 30 DAY) ORDER BY date,company");
            $news_list .= '<tr><td><font size="-1">' . $company['company'] . '</font></td><td>&nbsp;</td><td>&nbsp;</td><tr>';
            foreach ($result as $row) {
                $news_list .= '<tr><td>&nbsp;</td><td><font size="-1">' . $row['date'];
                if ($row['end'] && $row['end'] !== '0000-00-00') {
                    $news_list .= ' to ' . $row['end'];
                }
                $news_list .= '</font></td><td><font size="-1">' . stripslashes($row['description']) . '</font></td><tr>';
            }
        }
        $news_list .= '</table></font>';
    }

    //create service message body for this month
//	$query="SELECT * FROM holidays WHERE date BETWEEN '$setdate' AND DATE_ADD('$setdate',INTERVAL 30 DAY) ORDER BY date";
    $rows = tldUtils::getSqlToAssocArray("SELECT *, IF(service.customer_id > 0,
        (SELECT customers.customer_name FROM customers WHERE customers.id=service.customer_id),
        customer_name
    ) AS user_customer_display FROM service WHERE date_shipped BETWEEN DATE_SUB('$setdate',INTERVAL 30 DAY) AND DATE_ADD('$setdate',INTERVAL 30 DAY) ORDER BY customer_name,date_shipped ASC");
    $service_message .= '<p><b><a name="shipments"></a>Equipment Shipments +/- one month</b><p>';
    if (empty($rows)) {
        $service_message .= "<p>No equipment records to send for $setdate<p>";
    } else {
        //create message body
        $report = new tldReportColumnar($rows,
            ['xItems' => ['id' => 'Equip Record#',
                'user_customer_display' => 'Customer',
                'model' => 'Model',
                'location_short' => 'Location',
                'man_location' => 'Factory',
                'date_shipped' => 'Date Shipped'
            ],
                'links' => ['id' => 'http://www.tld-gse.com/en/private/product_support/equipment/equipment_admin.php?mode=record_view&form_type=main_tpl&id=']
            ]);
        $service_message .= $report->fetch();
    }
    //create warranty list
    $rows = tldUtils::getSqlToAssocArray("SELECT * FROM warranty WHERE warranty_status='PENDING' ORDER BY man_location,customer_name,claim_date ASC");
    $service_message .= '<p><b><a name="warranties"></a>Pending warranties</b><p>';
    if (empty($rows)) {
        $service_message .= "No warranties to send for $setdate<p>";
    } else {
        //create message body
        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => ['id' => 'WC#',
                    'man_location' => 'Factory',
                    'customer_name' => 'Customer',
                    'serial_number' => 'SN',
                    'claim_date' => 'Claim Date'
                ],
                'links' => ['id' => 'http://www.tld-gse.com/en/private/product_support/wc/wc_admin.php?mode=record_view&form_type=main_tpl&id=']
            ]);
        $service_message .= $report->fetch();
    }

    // create service bulletin list
    $rows = tldUtils::getSqlToAssocArray('SELECT sb.*, locations.business_unit FROM sb LEFT JOIN locations ON bu_id=locations.id ORDER BY sb.id DESC LIMIT 10');
    $service_message .= '<p><b><a name="bulletins"></a>Last 10 Bulletins</b><p>';
    if (empty($rows)) {
        $service_message .= "<p>No service bulletins to send for $setdate<p>";
    } else {
        //create message body
        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => [
                    'id' => 'SB#',
                    'business_unit' => 'Factory',
                    'dt' => 'Date',
                    'category' => 'Urgency',
                    'title' => 'Title'
                ],
                'links' => ['id' => 'http://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=sbs&m[1]=view&id=']
            ]
        );
        $service_message .= $report->fetch();
    }
    $rows = tldSR::byConstraints("sr.date_entered='$setdate'");
    $service_message .= <<<EOF
	<p><b><a name="service_records"></a>Service Records entered today</b><p>
EOF;
    if (empty($rows)) {
        $service_message .= "<p>No service Records to send for $setdate<p>";
    } else {
        //create message body
        $report = new tldReportColumnar(
            $rows,
            [
                'xItems' => [
                    'id' => 'SR#',
                    'sn' => 'SN',
                    'model' => 'Model',
                    'technician' => 'Technician',
                    'date' => 'Work Date',
                    'work_type' => 'Work Type',
                    'reason' => 'Reason'
                ],
                'links' => ['id' => 'http://www.tld-gse.com/en/private/product_support/equipment/equipment_admin.php?mode=record_view&form_type=service_lines_tpl&id=']
            ]
        );
        $service_message .= $report->fetch();
    }

    // don't send if $message_body is empty
    if (empty($news_list) && empty($service_message)) {
        exit;
    }

    if (!$cli) {
        echo $news_list;
        echo '<p>';
        echo $service_message;
        echo '<p>';
    }

    //create the recipient list
    $user_rows = tldGroup::getUserlistByMultipleGroup(['acl_auth_INTRANET'], null, ['where' => "T1.email != ''"]);

    foreach ($user_rows as $user_row) {
        $message_body = $general_toc;
        $message_body .= $service_toc;
        $message_body .= $news_list;
        $message_body .= $service_message;
        if (empty($message_body)) {
            continue;
        }
        // Check if not a test
        if (empty($test)) {
            $e = tldUtils::emailAttachment(
                $user_row['email'],
                'noreply@tld-gse.com',
                'ALVEST Daily Newsletter ' . $setdate,
                "<html><head>$_CSS</head><body>$message_body</body></html>"
            );
        }
        if ($e) {
            $logFormatter('Sent to: ' . $user_row['email']);
        } else {
            $logFormatter('ERROR: problem sending to: ' . $user_row['email']);
        }
    }
}
?>

</body>
</html>
