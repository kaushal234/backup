<?php
include_once('common.inc.php');
include_once('sales_service.inc.php');
include_once('forms_and_reports.inc.php');
include_once('Image/Graph.php');

ini_set('error_log', "$CRON_CACHE_DIR/monthly.bat.log");
error_log('RUNNING toc.kpi.php');

// Get previous year month period
$today = new DateTime(date('Y-m-d'));
$today->sub(new DateInterval('P1M'));
$dt_begin = date('Y-m-d h:i:s A');
$STATS = [];

// Get Listing
$kpi_type = ['nto', 'nts', 'tir', 'tat', 'tol'];

// KPI AEROSPECIALTIES TLD EUR/AME #
foreach ([7,11] as $locationId) {
    $location = new tldLocation($locationId);
    $opts['join'] = 'LEFT JOIN people ON people.id=toc.assid';
    $constraints = ['ssoid' => '45', 'people.div_id' => $location->itsDetails['juridical_location_id']];
    $locationName = $location->getBuName();
    $files = [];
    $html = '';
    foreach ($kpi_type as $type) {
        $options = [
            'graphTitle'  => sprintf('TOC KPI - %s - %s', $type,$locationName),
            'graphStyle'  => 'bar',
            'showMarkers' => 'true'
        ];

        switch ($type) {
            case 'nto':
                $rows = tldTOC::getNtoByConstraints($constraints, $opts);
                if(empty(array_filter(array_column($rows, 'yval')))){
                    $STATS[] = "<b>No items for $locationName</b><br>";
                    continue 3;
                }
                break;
            case 'nts':
                $rows = tldTOC::getNtsByConstraints($constraints, $opts);
                break;
            case 'tir':
                $rows = tldTOC::getTirByConstraints($constraints, $opts);
                break;
            case 'tat':
                $rows = tldTOC::getTatByConstraints($constraints, $opts);
                break;
            case 'tol':
                $rows = tldTOC::getTolByConstraints($constraints, $opts);
                break;
        }
        /**
         * @var Image_Graph $Graph
         */
        $Graph    = _doBarGraph($rows, $options);
        $filePath = sys_get_temp_dir() . '/' . $type . time();
        $Graph->done(['filename' => $filePath]);
        $html .= sprintf('<img src="%s"/>', $filePath);
        $files[] = $filePath;
    }
    $date = date('Y-m-d');
    $subject = sprintf('TOC KPI: AEROSPECIALTIES - %s', $locationName);
    $html = <<<HTML
<html>
    <body>
    <h3 align="center">$subject</h3>
    <h4 align="center">$date</h4>
    $html
    </body>
</html>
HTML;

    $html2pdf = new tldHTML2PDF($html, ['encoding' => 'utf-8']);
    $pdfFilename = sprintf('/tmp/kpi_aerospecialities_%s_%s.pdf', $locationName, date('Ymd'));
    rename($html2pdf->itsConvertedPdfFile->getFilePath(),$pdfFilename);
    // Send email
    $to = [];
    foreach([$location->getERP(), 250, 900] as $erp){
        $to[] = (new tldGroup('ROLE_TOC_AERO_NOT',$erp))->getEmailList();
    }
    $to = implode(',',array_unique(array_filter(array_merge(... $to))));
    $body = "Dear $locationName,<br> please find attached the previous month KPIs regarding the <b>Aerospeciaties</b> TOCs under your management";
    try {
        tldUtils::emailAttachment($to, 'noreply@tld-gse.com', $subject,$body, $pdfFilename);
        $STATS[] = "<b>Email sent to</b> $to <br><b>Subject:</b> $subject<br>" ;
    } catch (Exception $e) {
        $STATS[] = '<b>Email error:</b> ' . $e->getMessage() . "<b>Subject:</b> $subject<br>";
    }
    @unlink($pdfFilename);
    @unlink(str_replace('.pdf','', $html2pdf->itsConvertedPdfFile->getFilePath()));
    @unlink($html2pdf->itsHtmlTempFile->getFilePath());
    foreach($files as $file) {
        @unlink($file);
    }
}

// SCRIPT STATS
$dt_end = date('Y-m-d h:i:s A');
$STATS = implode('<br>',$STATS);
$body = <<<EOF
BEGIN AT $dt_begin<br>
FINISHED AT $dt_end<br><br>
$STATS
EOF;

$e = tldUtils::emailAttachment(
    'devteam@tld-america.com',
    'noreply@tld-gse.com',
    '[TLD SCRIPT] STATS of toc.kpi.php',
    $body
);
error_log('END OF toc.kpi.php');

function _doBarGraph($rows,$options){
    global $COLOURS;

    $COLOURS = array_keys(tldUtils::getColorNames());
    $Graph =& Image_Graph::factory('graph', [900, 500]);

    // add font
    $Font =& $Graph->addNew('ttf_font', 'DejaVuLGCSans-Bold.ttf');
    // set the font size
    $Font->setSize(10);
    $Graph->setFont($Font);
    $Plotarea = Image_Graph::factory('plotarea');
    $Legend = Image_Graph::factory('legend');
    $Graph->add(
        Image_Graph::vertical(
            Image_Graph::factory('title', array($options['graphTitle'], 14)),
            Image_Graph::horizontal(
                $Plotarea,
                $Legend,
                85
            ),
            10
        )
    );

    // link the legend with the plotares
    $Legend->setPlotarea($Plotarea);
    $Legend->setShowMarker = true;

    // create a grid and assign it to the secondary Y axis
    $GridY2 =& $Plotarea->addNew('line_grid', IMAGE_GRAPH_AXIS_Y);

    if(empty($rows)){
        $rows= [['factories' => 'NA', 'xval' =>0, 'yval' =>0]];
    }

    foreach($rows as $row){
        $erps[$row['factories']][] = $row;
    }

    $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
    //if specific erp is not set then do all erps
    $i = 1;
    foreach($erps as $factories=>$rows){
        $datasets[$factories] =& Image_Graph::factory('dataset');
        $datasets[$factories]->setName($factories);
        foreach($rows as $row){
            $datasets[$factories]->addPoint($row['xval'], $row['yval'], $factories);
        }
        $FillArray->addColor($COLOURS[$i], $factories);
        $i++;
    }

    $Plot =& $Plotarea->addNew($options['graphStyle'], [$datasets, 'stacked']);
    $Plot->setFillStyle($FillArray);

    if($options['showMarkers'] === 'true'){
        // create a Y data value marker
        $Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
        // and use the marker on the 1st plot
        $Plot->setMarker($Marker);
        $Plot->setDataSelector(Image_Graph::factory('Image_Graph_DataSelector_NoZeros'));

    }
    return $Graph;
}
