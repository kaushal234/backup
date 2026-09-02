<?php
use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;

$dateTime = new \DateTime($date ?? '');
$dateTime->setTime(23, 59, 59);

$container = $kernel->getContainer();
$client = $container->get(Client::class);

switch ($m[1]) {
    case 'drawing':
        if (empty($item) || empty($erp)) {
            $DEFAULT_ERROR[] = 'ERROR: Part Number or Company not set';
            break;
        }

        try {
            $fileStremResponse = new FileStreamedResponseFactory($client);

            $dateTime->setTime(23, 59, 59);
            $dateTime = $dateTime->format(\DateTimeInterface::ATOM);

            $response = $fileStremResponse->create(
                sprintf('ion/bill-of-materials/drawings/site=%d;project=%s;product=%s', $erp, $project, $item),
                ['query' => ['date' => $dateTime]]
            );

            $response->send();
            exit;
        } catch (ClientException|ServerException $exception) {
            $DEFAULT_ERROR[] = sprintf('ERROR:  No file found for %s.', $item);
        }
        break;
    case 'drawing_3d_files':
        if (empty($item) || empty($erp)) {
            $DEFAULT_ERROR[] = 'ERROR: Part Number or Company not set';
            break;
        }

        try {
            $fileStremResponse = new FileStreamedResponseFactory($client);

            $dateTime->setTime(23, 59, 59);
            $dateTime = $dateTime->format(\DateTimeInterface::ATOM);

            $response = $fileStremResponse->create(
                sprintf('ion/bill-of-materials/drawing_3d_files/site=%d;project=%s;product=%s', $erp, $project, $item),
                ['query' => ['date' => $dateTime]]
            );

            $response->send();
            exit;
        } catch (ClientException|ServerException $exception) {
            $DEFAULT_ERROR[] = sprintf('ERROR:  No 3d files found for %s.', $item);
        }
        break;
    case 'jpg':
        $aReleasedController = new tldReleasedController();
        $aReleasedController->outFileInVault($erp, str_replace('PDF', 'JPG', $item));
        exit;
    case 'photo':
        if (empty($erp) || empty($item)) {
            $DEFAULT_ERROR[] = 'ERROR: ERP# or PN not set';
            break;
        }
        $photoController = new tldPhotoController();
        if (isset($key)) {
            $folderpath = $photoController->fileExistsInVault($erp, $item);
            $files = $photoController->getDirFilelist($erp, $folderpath);
            $file = "$item/" . $files[$key];
        } else {
            $file = "$item.jpg";
        }
        $photoController->outFileInVault($erp, $file, ['width' => $new_width]);
        exit;
    case 'file':
        if (!$item) {
            $DEFAULT_ERROR[] = "ERROR: No file found for $item";
            break;
        }

        $aReleasedController = new tldReleasedController();
        $aReleasedController->outFileInVault($erp, $item);
        $template = 'NO_TEMPLATE';
        break;
}
