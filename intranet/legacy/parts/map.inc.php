<?php
session_start();
if (!isset($_SESSION['sess'])) {
    $_SESSION['sess'] = null;
}
$sess =& $_SESSION['sess'];

require_once('Image/GraphViz.php');


$colors = ["aliceblue", "antiquewhite", "aquamarine", "azure", "beige", "bisque", "black", "blue", "blueviolet",
    "brown", "burlywood", "cadetblue", "chartreuse", "chocolate", "coral", "cornflowerblue", "cornsilk"];
$DEFAULT_MENUTEMP .= <<<EOF
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="$php_self?m[0]=sor&m[1]=view&m[2]=map&id=$id">Overview</a>
&nbsp;|&nbsp;<a href="$php_self?m[0]=sor&m[1]=view&m[2]=map&m[3]=sales&id=$id">Customer Facing</a>
EOF;

$rows = &$sess['parts']['solbyuser'];
if (!empty($rows)) {
    $graph = new Image_GraphViz(
        true,
        [
            "fontsize" => 8,
            "size" => "11,17",
            "dim" => 3,
            "ratio" => "auto",
            "rankdir" => "",
        ]
    );
    foreach ($rows as $row) {
        $graph->addNode(
            "SO".$row['t_orno'],
            [
                'tooltip' => '',
                'shape' => 'plaintext',
            ],
            "CUNO".$row['t_cuno']
        );

        $graph->addEdge(
            ["SO".trim($row['t_orno']) => "ITEM".str_replace("-", "_", $row['t_item'])],
            ['label' => '']
        );

    }
}

switch ($type ?? null) {
    case 'gif':
        $graph->image("gif");
        exit;
    case 'vrml':
        $graph->image("vrml");
        exit;
    case 'svg':
        $graph->image("svg");
        exit;
    default:
        $cmapx = $graph->fetch("cmapx");
        $body .= <<<EOF
	<h3>SOR Map</h3>
	<IMG SRC="$php_self?m[3]=${m[3]}&type=svg&id=$id" USEMAP="#G" />
	$cmapx
	<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
	<a href="$php_self?m[3]=&type=vrml&id=$id">VRML</a>
EOF;
}

echo $body;

