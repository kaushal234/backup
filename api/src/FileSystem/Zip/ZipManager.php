<?php

declare(strict_types=1);

namespace App\FileSystem\Zip;

use App\FileSystem\Zip\ContextProviders\ZipAdapterContextProviderInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

readonly class ZipManager
{
    public function __construct(private ZipAdapter $adapter)
    {
    }

    public function createZipResponse(object $object, bool $flatStructure = false, array $formats = []): Response
    {
        /** @var ZipAdapterContextProviderInterface $contextProvider */
        $contextProvider = $this->adapter->getContextProvider();

        $files = $contextProvider->getFiles($object, $formats);

        if (empty(array_filter($files))) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $filepath = tempnam(sys_get_temp_dir(), 'zip');

        if (false === $filepath) {
            throw new \RuntimeException('Impossible to create the temporary ZIP file.');
        }

        $zip = $this->adapter->createZip($filepath, $files, $flatStructure);

        $numberOfFiles = $zip->numFiles;
        $zip->close();

        if ($numberOfFiles > 0) {
            return (new BinaryFileResponse($filepath, Response::HTTP_OK, [
                'Content-Type' => 'application/zip',
                'Content-Length' => filesize($filepath),
            ]))->setContentDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                \sprintf('%s.zip', $contextProvider->getArchiveName($object)),
                'alvest.zip'
            )->deleteFileAfterSend();
        }

        unlink($filepath);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
