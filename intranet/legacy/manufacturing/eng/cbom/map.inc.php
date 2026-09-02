<?php
include_once("Image/GraphViz.php");
$graph = new Image_GraphViz(true, array("fontsize"=>8,
			"size"=>"11,17",
			"dim"=>3,
			"rankdir"=>"LR")
			);

foreach(array("POR","OP") as $colorid=>$cluster){
	$graph->addCluster($cluster,
		$cluster,
		array(
			'label' => $cluster,
			'bgcolor'=>$colors[$colorid]
		)
	);
}

$pors = tldPOR::byPRJ($sn, $erp);
if(count($pors)){
	foreach($pors as $por){
		$graph->addNode("POR".$por['t_pdno'],
			array(
				'label' => "POR #".$por['t_pdno'],
				'shape'=>'box'
			),
			"POR"
		);
		$ops = tldOP::byPDNOERP($por['t_pdno'], $erp);
		if(count($ops)){
			foreach($ops as $op){
				$graph->addNode("OP".$op['t_opno'],
					array(
						'label' => "OP #".$op['t_opno'].
									'\nAct. Hrs. '.$op['hrem_tot'].
									'\nEsts Hrs. '.$op['t_hrem'],
						'shape'=>'box'
					),
					"OP"
				);
				$graph->addEdge(
							array("POR".$por['t_pdno']=> "OP".$op['t_opno']),
							array('label' => '')
							);
			}
		}
	}
}

switch($type){
case 'gif':
	$graph->image("gif");
	exit;
break;
case 'vrml':
	$graph->image("vrml");
	exit;
break;
case 'svg':
	$graph->image("svg");
	exit;
break;
default:
	$cmapx = $graph->fetch("cmapx");
	$body .=<<<EOF
	<h3>Project Map</h3>
	<IMG SRC="$php_self?m[0]=cbom&m[1]=view&m[2]=map&m[3]=${m[3]}&type=svg&erp=$erp&date=$date&sn=$sn" USEMAP="#G" />
	$cmapx
	<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
	<a href="$php_self?m[0]=sor&m[1]=view&m[2]=map&type=vrml&id=$id">VRML</a>
EOF;
}
