<?php

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\StreamedResponse;

switch($m[1] ?? null){
    case 'drawing':
    case 'PIdrawing':
        $dateTime = ($date ?? null) ? new \DateTime($date) : null;

        if ($dateTime instanceof \DateTime) {
            $dateTime->setTime(23, 59, 59);
        }

        try {
            if ('PIdrawing' === $m[1]) {
                $dateTime = new \DateTime($sess["bom"]->itsDate ?? '');
            }

            $file = $client->request(
                'GET',
                sprintf('/ion/bill-of-materials/drawings/site=%d;project=;product=%s', $ERP, $item),
                ['query' => ['date' => $dateTime ? $dateTime->format(\DateTimeInterface::ATOM) : null]]
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
        }
        break;

    case 'PIHelp';
        if(empty($file_id)) {
            exit;
        }
        $file = new tldFile($file_id);
        if($file->isEmpty()){
            $DEFAULT_ERROR[]=  "ERROR: No File #$file_id found...";
            break;
        }
        $file->download();
        break;
}

if ($file) {
	$aReleasedController = new tldReleasedController();
	$aReleasedController->outFileInVault($ERP,$file);
	$template="NO_TEMPLATE";
} else {
	$DEFAULT_ERROR[] = sprintf(_("ERROR: No file found for %s"),$item);
}
