<?php
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
include_once('dms.inc.php');

$requestId = $_GET['id'];
if (!$requestId) {
    throw new NotFoundHttpException('Photo not found');
}

//DMS 6191 is the DMS for ethic sequence, so it should be downloadable
if (6191 !== (int) $requestId) {
    $results= tldDMS::byConstraints(['id' => $requestId, 'type_id' => '9', 'access_type' => 'PUBLIC', 'status NOT' => 'ARCHIVE'], ['limit' => 1]);
    if (empty($results)) {
        throw new NotFoundHttpException('Photo not found');
    }
}

$id = 6191 !== (int) $requestId ? $results[0]['id'] : 6191;

$dms = new tldDMS($id);
if ($dms->isEmpty()) {
    throw new NotFoundHttpException('Photo not found');
}

$revActual = $dms->getActiveRevisionObj();
if($revActual->isEmpty() || (int) $revActual->getPubFileID() === 0){
    throw new NotFoundHttpException('Photo not found');
}

$file = new tldFile($revActual->getPubFileID());
if($id !== 6191 && (!$file->isFile() || 'jpg' !== $file->getExtension())){
    throw new NotFoundHttpException('Photo not found');
}

return $file->download();
