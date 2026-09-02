<?php
include_once("common.inc.php");
include_once("forms_and_reports.inc.php");
include_once("sales_service.inc.php");

ini_set("error_log", "$CRON_CACHE_DIR/weekly.bat.log");

/**
 *  LATE TOC NOTIFICATION SCRIPT
 *
 *  Send notification every sunday
 *  Notify all customer that have opened TOC
 *
 **/

$STATS = [];
$dt_begin = date("Y-m-d h:i:s A");
error_log("RUNNING $PHP_SELF");

// SEND TOC Open/In progress to all extranet contact

error_log("SEND Open/In progress to all extranet contact");

$query = <<<EOF
SELECT DISTINCT
    cons.email AS email,
    CONCAT(cons.lastname, ' ', cons.firstname) AS fullname,
    IF(cons.lang = "",
        'en',
        cons.lang
    ) AS lang
FROM extranet_users AS cons
    INNER JOIN toc ON cons.id=toc.conid
WHERE
    cons.email <> ""
    AND cons.enable = 'Y'
    AND toc.status NOT IN('SOLVED','CLOSED')
    AND (SELECT count(*) FROM extranet_users_roles AS roles WHERE roles.parent_id=cons.id AND role='fl_NOT_WTOC')
EOF;
$extranetUserRows = tldUtils::getSqlToAssocArray($query);

foreach ($extranetUserRows as $user) {
    extract($user);
    $email = trim($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        continue;
    }
     $rows = _tocByConstraints("cons.email = '$email' ");

    if (empty($rows)) {
        continue;
    }

    // Prepare email
    $TO = $email;
    $CC = "";
    $SUBJECT = "TOC Weekly Notification for " . htmlentities($fullname);
    $BODY = "Please find the recap of your TOC <br><br>";
    $BODY .= _getListing($rows, "TOC for $fullname");
    // Send email
    $e = _sendEmail($TO, $SUBJECT, $BODY, $CC);
    $STATS[] = "Email sent to $TO for TOC in progress from $fullname";
}

// Methods

function _tocByConstraints($a)
{
    if (empty($a)) return;

    $query = <<<EOF
SELECT
    cons.email AS con_email,
    ers.sn,
    ers.model,
    ers.airport_code,
    ers.cust_asset_num,
    toc.*,
    IF(locations.location IS NULL,
		'NO SSO',
		locations.location
	) AS sso_fullname,
	(SELECT CONCAT(lastname,', ',firstname) FROM people
		WHERE people.id=tecid
	) AS tec_fullname
FROM toc
    LEFT JOIN locations ON toc.ssoid=locations.id
    LEFT JOIN service AS ers ON toc.erid=ers.id
    LEFT JOIN extranet_users AS cons ON toc.conid=cons.id
WHERE
    toc.status NOT IN('SOLVED','CLOSED')
GROUP BY toc.id
HAVING $a
ORDER BY toc.dt
EOF;
    $rows = tldUtils::getSqlToAssocArray($query);
    // Get last log
    foreach ($rows as $k => $row) {
        $toc = new tldTOC($row['id']);
        $logs = $toc->getNotificationFullLog();
        $rows[$k]['last_log'] = <<<EOF
{$logs[0]['poster_fullname']}
{$logs[0]['module']}:
{$logs[0]['comment']}
EOF;
    }
    return $rows;
}

function _sendEmail($TO, $SUBJECT, $BODY, $CC)
{
    error_log("Sending email to $TO cc $CC");
    return tldUtils::emailAttachment(
        $TO,
        'noreply@tld-gse.com',
        $SUBJECT,
        $BODY,
        NULL,
        $CC
    );
}

function _getListing($rows, $title)
{
    $report = new tldReportColumnar(
        $rows,
        [
            "xItems" => [
                "id" => "TOC#",
                "sso_fullname"	=>"SSO",
                "tec_fullname"	=>"Service Technician",
                "status" => "Status",
                "sn" => "SN",
                "cust_asset_num" => "Customer asset #",
                "model" => "Model",
                "short_desc" => "Short Problem Description",
                "dt" => "Claim date",
                "last_log" => "Last Log",
            ],
            "title" => $title,
//            "links" => ["id" => "http://www.tld-gse.com/extranet/index.php?m[0]=toc&m[1]=view&id="],
        ]
    );
    return $report->fetch();
}

$dt_end = date("Y-m-d h:i:s A");
error_log("END OF RUNNING $PHP_SELF");

// STATS of the script
$STATS = implode("<br>", $STATS);
$BODY = <<<EOF
BEGIN AT $dt_begin<br>
FINISHED AT $dt_end<br><br>
$STATS
EOF;

// SEND email with STATS
$e = tldUtils::emailAttachment(
    "devteam@tld-america.com",
    'noreply@tld-gse.com',
    "[TLD SCRIPT] STATS of $PHP_SELF",
    $BODY
);
