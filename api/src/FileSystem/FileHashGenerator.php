<?php

declare(strict_types=1);

namespace App\FileSystem;

class FileHashGenerator
{
    public function hash(\SplFileInfo $file): string
    {
        return hash_file('sha256', $file->getRealPath());
    }
}
