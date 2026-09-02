<?php
$DEFAULT_TITLE.= "\People";
$DEFAULT_MENU.= <<<EOF
    <tld:include src="directory/people/_menulegacy.html.twig" />
EOF;

switch($m[1]){
case 'view':
    include("people.view.inc.php");
break;
default:
    $body = <<<EOF
<h3>People module</h3>
<p>Welcome to people module</p>
<ul><li><a href="/en/private/calendar/calendar.php?m[0]=seq&m[1]=new&m[2]=hr.user.new">Start NEW user request sequence</a> (IT systems profile)</li></ul>
EOF;
    $matrix = new tldMatrix(
        tldSEQ::countByHRTemplateByDivisionByConstraints(array("status"=>"OPEN")),
        "division", "template", "num",
        "/en/private/calendar/calendar.php?m[0]=seq&m[1]=listing&m[2]=byOpenByHRTemplateByDivision",
    	"OPEN HR User Sequence by Division"
    );
    $body.= $matrix->fetch();
break;
}





?>
