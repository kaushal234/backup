<?php
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\StreamedResponse;

$DEFAULT_TITLE .= "\BOM";

switch($m[1] ?? null){
case 'view':
	if($reset ?? null){
		$sess["bom"] = [];
		$sess["history"] = [];
	}
	if(isset($history)){
		$pn = $sess["history"][$history];
		$sess["history"] = array_slice($sess["history"],0,$history + 1);
	}elseif(empty($sess["history"]) || !in_array($pn, $sess["history"])){
		$sess["history"][] = $pn;
	}

    $dateTime = new \DateTime($date ?? '');
    $dateTime->setTime(23, 59, 59);
    try {
        $bom = $client->request(
            'GET',
            sprintf('/ion/bill-of-materials/shopfloor_views/site=%d;project=%s;product=%s', $ERP, $project ?? null, $pn),
            [
                'query' => [
                    'date' => $dateTime->format(\DateTimeInterface::ATOM),
                    'otherLanguage' => 'CH' === $LANG ? 'zh' : mb_strtolower($LANG),
                ]
            ]
        )->toArray();
    } catch (ClientException $exception) {
        $DEFAULT_ERROR[] = _("ERROR: BOM not found.");
        break;
    }

    $sess["bom"] = new tldBOM($ERP, $pn, $date ?? '', false, ['lang' => $LANG], $bom);

    $urlview = sprintf("&m[1]=view&pn=%s&date=%s&project=%s&multi=%s", $pn, $date ?? '', $project ?? null, $multi ?? null);

	$DEFAULT_TITLE .= "\\$pn";
	$DEFAULT_MENU .="
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href=\"$php_self?m[0]=bom$urlview\">"._("Home")." BOM</a>&nbsp;|&nbsp;
<a href=\"$php_self?m[0]=bom$urlview&m[2]=pdf\">"._("PDF (TESTING)")."</a>
";

	switch($m[2] ?? null){
	case 'pdf':
        try {
            $file = $client->request(
                'GET',
                sprintf('ion/bill-of-materials/shopfloor_pdfs/site=%d;project=%s;product=%s', $ERP, $project, $pn),
                [
                    'headers' => ['Accept' => 'application/pdf'],
                    'query' => [
                        'date' => $dateTime->format(\DateTimeInterface::ATOM),
                    ],
                ]
            );

            if (200 !== $file->getStatusCode()) {
                throw new ClientException($file);
            }

            $response = new StreamedResponse(static function () use ($file) {
                echo $file->getContent();
            });

            $response->headers->set('content-disposition', $file->getHeaders()['content-disposition']);
            $response->headers->set('content-type', $file->getHeaders()['content-type']);
            $response->send();
            exit;
        } catch (ClientException $exception) {
            $file = null;
            $DEFAULT_ERROR[] = "ERROR: BOM not found.";
        }
        break;
	default:
    	if(count($sess["bom"]->itsBOMAsArray) < 1){
    	    $DEFAULT_ERROR[] = sprintf(_("ERROR: No BOM founded for the ERP#%s with PN#%s"),$ERP,$pn);
    		break;
    	}
    	// EAP Flag check
    	foreach($sess["bom"]->itsBOMAsArray as $key=>$line){
    		$eaps = tldEAP::byOpenPartNumber($line['t_sitm']);
    		if($eaps){
    			$sess["bom"]->itsBOMAsArray[$key]['eap'] = 1;
    			$eap_list = array();
    			foreach($eaps as $eap){
    				$eap_list[] = $eap['id'];
    			}
    			$sess["bom"]->itsBOMAsArray[$key]['eap_num'] = implode(", ",$eap_list);
    		}
    	}
    	$body = include("$PATH/view.bom.tpl.inc.php");
	break;
	}
    break;
default:
	$body = include("$PATH/homepage.bom.tpl.inc.php");
break;
}
