<?php

declare(strict_types=1);

namespace App\FileSystem\Storage;

interface FileStorageHandlerInterface
{
    public function save(string $filename, \SplFileObject $filecontent);

    public function copy(string $filename, \SplFileObject $filecontent);

    public function remove(?string $filepath);
}
