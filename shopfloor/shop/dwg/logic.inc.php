<?php
use Symfony\Component\HttpClient\Exception\ClientException;

$DEFAULT_TITLE .= "\Drawings";

$erp = $_GET['erp'] ?? null;
$id = $_GET['id'] ?? null;

switch($m[1] ?? null){
    case 'ls':
        try {
            $engineeringRevisions = $client->request(
                'GET',
                sprintf('/ion/engineering_items/item=%s;project=', $id))->toArray();
            usort($engineeringRevisions['revisions'], function($firstRevision, $latestRevision){
                return (strtotime($firstRevision['effectiveDate']) < strtotime($latestRevision['effectiveDate'])) ? 1 : -1;
            });
        } catch (ClientException $exception) {
            $DEFAULT_ERROR[] = _("ERROR: Item not found.");
            break;
        }

        if($engineeringRevisions ?? false){
            $body = include("$PATH/view.dwg.tpl.inc.php");
        }
        else {
            $DEFAULT_ERROR[] = _("ERROR: No Drawing Revision Details to show");
        }
    break;
    case 'outFile':
        $DEFAULT_ERROR[] = 'ERROR : This is no longer used';
    default:
        $body = include("$PATH/homepage.dwg.tpl.inc.php");
    break;
}
