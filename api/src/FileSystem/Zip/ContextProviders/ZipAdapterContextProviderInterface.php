<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\ContextProviders;

use App\FileSystem\AdapterContextProviderInterface;
use App\FileSystem\Zip\Report\ZipReport;

interface ZipAdapterContextProviderInterface extends AdapterContextProviderInterface
{
    public function getFiles(object $subject, array $formats = []): array;

    public function processZip(\ZipArchive $zip, array $files, ZipReport $report, bool $flat = false);

    public function getArchiveName(object $subject): ?string;
}
