<?php

declare(strict_types=1);

namespace App\FileSystem\Zip;

use App\FileSystem\Zip\Report\ZipReport;

trait ProcessZipSimpleFolderTrait
{
    public function processZip(\ZipArchive $zip, array $files, ZipReport $report, bool $flat = false): void
    {
        foreach ($files as $zipName => $realPath) {
            $report->addFile($zip, $realPath, $zipName);
        }
    }
}
