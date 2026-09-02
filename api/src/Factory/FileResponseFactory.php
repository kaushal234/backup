<?php

declare(strict_types=1);

namespace App\Factory;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;

class FileResponseFactory
{
    public function createFileResponse(File $file): BinaryFileResponse
    {
        $response = new BinaryFileResponse($file);
        BinaryFileResponse::trustXSendfileTypeHeader();
        $response->headers->set('Content-Type', $file->getMimeType());

        return $response;
    }
}
