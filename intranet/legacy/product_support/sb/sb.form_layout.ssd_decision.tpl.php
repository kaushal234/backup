<?php
ob_start();

// Listing of decision choice
// -- Parts
$partsDecisionList = tldSB_Line::getPartDecisionList();
if ($sb->isPartsNotNeeded()) {
    unset($partsDecisionList['A'], $partsDecisionList['B'], $partsDecisionList['C']);
}
if ($sb->isPartsNeeded()) {
    unset($partsDecisionList['D']);
}
if ($sb->isConfidential()) {
    unset($partsDecisionList['B'], $partsDecisionList['C']);
}
$partsDecisionListAsShortDescShortDesc = array_combine(
    array_keys($partsDecisionList),
    array_keys($partsDecisionList)
);
// -- Service
$serviceDecisionList = tldSB_Line::getServiceDecisionList();
if ($sb->isConfidential()) {
    unset($serviceDecisionList['2'], $serviceDecisionList['3'], $serviceDecisionList['4']);
}
$serviceDecisionListAsShortDescShortDesc = array_combine(
    array_keys($serviceDecisionList),
    array_keys($serviceDecisionList)
);
?>

<!-- DISPLAY -->

<style type="text/css">
#form_menu {
    position:absolute;
    top: 50%;
    right: 5px;
    border: 2px solid grey;
    z-index: 100;
    background-color: white;
	padding: 10px;
}

.tld_table thead tr, .tld_table tfoot tr {
	background: #2971A8;
}
.tld_table thead tr th, .tld_table tfoot tr td {
	color: white;
	text-align: center;
}
.alert-message {
    display: block;
    padding: 5px;
    border-radius: 3px;
    border: 1px solid rgb(180, 180, 180);
    background-color: rgb(227, 83, 13);
    border-color: rgb(227, 83, 13);
}

</style>

<!-- FORM SSD_DECISION -->
<?php
$limitLine = 1500;
if (count($SB_LINES) > 1500) {
    $SB_LINES_CHUNKED = array_chunk($SB_LINES, 1500);
    $body .= '<div class="alert-message" ><p>This dashboard is split in '.count($SB_LINES_CHUNKED).' every '.$limitLine.' rows. Don\'t forget to submit all dashboards.</p></div>';

    foreach ($SB_LINES_CHUNKED as $chunk) {
        $SB_LINES = $chunk;
        include 'sb.form.ssd_decision.tpl.php';
    }
} else {
    include 'sb.form.ssd_decision.tpl.php';
}
?>

<div id="definitions">
<p>
  <h4>Definitions</h4>
  <u>Parts</u><br>
  <ul>
    <?php  foreach($partsDecisionList as $val=>$definition): ?>
    <li><?= $val ?> -> <?= $definition ?></li>
    <?php  endforeach; ?>
  </ul>
  <u>Service</u><br>
  <ul>
    <?php  foreach($serviceDecisionList as $val=>$definition): ?>
    <li><?= $val ?> -> <?= $definition ?></li>
    <?php  endforeach; ?>
  </ul>
</p>
</div>

<?php

// Functions ----------------------------------------------------->

function _getDefaultPartValue($SB_LINE){
    global $sb;
    if(!empty($SB_LINE['part_decision'])){
        return $SB_LINE['part_decision'];
    }
    return $sb->getDefaultPartDecision();
}

function _getDefaultServiceValue($SB_LINE){
    global $sb;
    if(!empty($SB_LINE['service_decision'])){
        return $SB_LINE['service_decision'];
    }
    return $sb->getDefaultServiceDecision();
}

function _getSelectInputToHTML($name,$list,$opt=array()){
    if($opt['attr']) $attr=" {$opt['attr']}";
    $selectInput=<<<EOF
<select name="$name"$attr>
EOF;
    foreach($list as $value=>$label){
        if($opt['default']==$value) $default=' selected="selected"';
        $selectInput.=<<<EOF
<option value="$value"$default>$label</option>
EOF;
        $default = NULL;
    }
    return $selectInput."</select>";
}

// End of Functions ----------------------------------------------------->

return ob_get_clean();

