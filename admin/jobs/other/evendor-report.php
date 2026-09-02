<?php
include_once("common.inc.php");
include_once("finance.inc.php");
include_once("erp.inc.php");
include_once("forms_and_reports.inc.php");

if(empty($argv[1]) || empty($argv[2])){
    error_log("ERROR: ERP company or currency not set.");
    exit;
}
$res = [];
$res["erp_id"] = [$argv[1]];
$res["curr"] = $argv[2];
$report_info = tldEvendorCompany::getReport($res["erp_id"]);
array_walk($report_info, function (&$item, $key, $res) {
    $item["money_12"] = $item["money_12"] * tldForex::getRate($item["currency"], $res["curr"]);
    $item["currency"] = $res["curr"];
    $item["approved"] = $item["approved"] ? "Approved" : "Disapproved";
    $item["val_erp"] = $item["erp"];
    $item["val_suno"] = $item["t_suno"];
}, $res);

$sort_info = [];
$to_unset = [];
foreach ($report_info as $key => $item) {
    $sort_info[$item["erp"]][$item["t_suno"]] = $item;
    $sort_info[$item["erp"]][$item["t_suno"]]["key"] = $key;
    if ($item["slave"] && !empty($item["master_suno"]) && !empty($item["master_erp"])) {
        $to_unset[] = ["to_suno" => $item["master_suno"], "to_erp" => $item["master_erp"], "from_suno" => $item["t_suno"], "from_erp" => $item["erp"]];
    }
}
$to_complete = [];
foreach ($to_unset as $un) {
    if (!empty($sort_info[$un["to_erp"]]) && !empty($sort_info[$un["to_erp"]][$un["to_suno"]])) {
        $key = $sort_info[$un["to_erp"]][$un["to_suno"]]["key"];
        $report_info[$key]["ncr"] += $sort_info[$un["from_erp"]][$un["from_suno"]]["ncr"];
        $report_info[$key]["money_12"] += $sort_info[$un["from_erp"]][$un["from_suno"]]["money_12"];
        $report_info[$key]["erp"] .= " \n" . $sort_info[$un["from_erp"]][$un["from_suno"]]["erp"];
        $report_info[$key]["t_suno"] .= " \n" . $sort_info[$un["from_erp"]][$un["from_suno"]]["t_suno"];
        $report_info[$key]["t_nama"] .= " \n" . $sort_info[$un["from_erp"]][$un["from_suno"]]["t_nama"];
        unset($report_info[$sort_info[$un["from_erp"]][$un["from_suno"]]["key"]]);
    }
    else {
        $to_complete[] = $un;
    }
}
$to_complete = tldEvendorCompany::getCompanyListClassificationInformation($to_complete);
foreach ($to_complete as $uncomp) {
    $key = $sort_info[$uncomp["from_erp"]][$uncomp["from_suno"]]["key"];
    $report_info[$key]["exper_name"] = $uncomp["info"]["exper_name"];
    $report_info[$key]["class_cost"] = $uncomp["info"]["class_cost"];
    $report_info[$key]["class_quality"] = $uncomp["info"]["class_quality"];
    $report_info[$key]["class_delivery"] = $uncomp["info"]["class_delivery"];
    $report_info[$key]["class_communication"] = $uncomp["info"]["class_communication"];
    $report_info[$key]["class_innovation"] = $uncomp["info"]["class_innovation"];
    $report_info[$key]["class_support"] = $uncomp["info"]["class_support"];
    $report_info[$key]["rank"] = $uncomp["info"]["rank"];
    $report_info[$key]["approved"] = $uncomp["info"]["approved"] ? "Approved" : "Disapproved";
    $report_info[$key]["last_eval"] = $uncomp["info"]["last_eval"];
    $report_info[$key]["next_eval"] = $uncomp["info"]["next_eval"];
    $report_info[$key]["comment"] = $uncomp["info"]["comment"];
}
$total = 0.0;
foreach ($report_info as $key => $row) {
    if ($row["money_12"] >= 0) {
        $total = $total + $row["money_12"];
    }
    $tmp_percent[$key] = $row['money_12'];
}
array_walk($report_info, function (&$item, $key, $total) {
    $item["percent"] = $item["money_12"] * 100;
    $item["percent"] = $item["percent"] / $total;
}, $total);
array_multisort($tmp_percent, SORT_DESC, $report_info);
$old_key = -1;
$mouseOver = [];
foreach ($report_info as $key => $row) {
    if ($old_key != -1) {
        if ($report_info[$key]["money_12"] >= 0) {
            $report_info[$key]["percent_cumul_money"] = $report_info[$key]["money_12"] + $report_info[$old_key]["percent_cumul_money"];
        } else {
            $report_info[$key]["percent_cumul_money"] = $report_info[$old_key]["percent_cumul_money"];
        }
        $report_info[$key]["percent_cumul"] = ($report_info[$key]["percent_cumul_money"] * 100) / $total;
    } else {
        $report_info[$key]["percent_cumul"] = $report_info[$key]["percent"];
        $report_info[$key]["percent_cumul_money"] = $report_info[$key]["money_12"];
    }
    $old_key = $key;
    if ($report_info[$key]["percent_cumul"] < 80) {
        $report_info[$key]["percent_ABC"] = 'A';
    } elseif ($report_info[$key]["percent_cumul"] < 95) {
        $report_info[$key]["percent_ABC"] = 'B';
    } else {
        $report_info[$key]["percent_ABC"] = 'C';
    }

    if (strlen($report_info[$key]["comment"]) > 80) {
        $p = explode(" ", $report_info[$key]["comment"], substr_count(substr($report_info[$key]["comment"], 0, 80), " "));
        if (count(explode(" ", $report_info[$key]["comment"])) > substr_count(substr($report_info[$key]["comment"], 0, 80), " ")) {
            $num = substr_count(substr($report_info[$key]["comment"], 0, 80), " ") - 1;
            $p[$num] = "...";
        }
    } else {
        $p = explode(" ", $report_info[$key]["comment"], 15);
    }
    $report_info[$key]["comment_show"] = implode(" ", $p);
    $tmp = $report_info[$key]['comment'];
    if (!empty($report_info[$key]['comment'])) {
        $tmp = nl2br($tmp);
        $tmp = TldDatabase::escape($tmp);
        $tmp = addslashes($tmp);
        $tmp = htmlentities($tmp);
        $tmp = str_replace("&lt;br /&gt;", "<br />", $tmp);
    }
    $mouseOver[] = 'comm.push("' . "javascript:overlib('" . $tmp . "', WIDTH, 350, OFFSETX, 50, VAUTO, FGCOLOR, '#eeeeee', BGCOLOR, 'gray', CAPCOLOR, '#dedede' );" . '");';
}
array_walk($report_info, function (&$item, $key) {
    $item["money_12"] = number_format($item["money_12"], 0, ",", " ");
    $item["percent_cumul"] = number_format($item["percent_cumul"], 0, ",", " ");
    $item["percent_cumul"] .= " %";
    $item["percent"] = number_format($item["percent"], 0, ",", " ");
    $item["percent"] .= " %";
    if ($item["percent"] == 0 && !empty($item["money_12"]) && $item["money_12"] > 0) {
        $item["percent"] = "<0.5 %";
    }
});

$report = new tldCSV(
    $report_info,
    [
        "xItems" => [
            "erp" => "ERP",
            "t_suno" => "Supplier Code",
            "t_nama" => "Supplier Name",
            "slave" => "Master",
            "exper_name" => "Supplier Expertise Level",
            "money_12" => "Total Revenue last 12 months in " . $res["curr"],
            "percent" => "% Revenue (half round up)",
            "percent_cumul" => "Cumulative % (half round up)",
            "percent_ABC" => "ABC (80/ 15/ 5%)",
            "ncr" => "NCR last 12 month",
            "class_cost" => "Cost",
            "class_quality" => "Quality",
            "class_delivery" => "Logistic",
            "class_communication" => "Communication, transparency and responsiveness Ethic",
            "class_innovation" => "Innovation & Partnership",
            "class_support" => "Product and field support",
            "rank" => "Supplier Classification",
            "approved" => "Supplier Status",
            "last_eval" => "Last review",
            "next_eval" => "Next review",
            "comment" => "Last Comment"
        ],
        "showTitles" => true,
    ]
);
$savePath = $UPLOADS_PATH."/evendorAnnualReport/";
if (!file_exists($savePath)) {
    mkdir($savePath);
}
$completePath = $savePath ."/" . date("Y-m-d")."_".$res["erp_id"][0].".csv";
$reportName = date("Y-m-d")."_".$res["erp_id"][0].".csv";
file_put_contents($completePath, $report->fetch());

$modfile = [];
$modfile['parent_id'] = $res["erp_id"][0];
$modfile['description'] = $reportName;
$modfile['module'] = "eVendor-R";
$modfile['poster'] = 2716;
$modfile['level'] = 0;
tldModFile::insert($modfile, ["tmp_name" => $completePath, "name" => $reportName]);

unlink($completePath);