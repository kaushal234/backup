<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace App\FileSystem\Zip;

use App\FileSystem\AbstractAdapter;
use App\FileSystem\Zip\ContextProviders\ZipAdapterContextProviderInterface;
use App\FileSystem\Zip\Report\ZipReport;

class ZipAdapter extends AbstractAdapter
{
    public function createZip(string $filepath, array $files, bool $flatStructure = false): \ZipArchive
    {
        $zip = new \ZipArchive();
        if (true !== $zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            throw new \RuntimeException(\sprintf('Could not open zip file %s', $filepath));
        }

        $report = new ZipReport();

        /** @var ZipAdapterContextProviderInterface $contextProvider */
        $contextProvider = $this->getContextProvider();
        $contextProvider->processZip($zip, $files, $report, $flatStructure);

        $zip->addFromString(
            '_export-manifest.json',
            json_encode($report->toArray(), \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR)
        );

        $zip->addFromString(
            '_README.txt',
            $report->toPlainText()
        );

        return $zip;
    }
}
