<?php

declare(strict_types=1);

namespace App\Factory;

interface VaultFileInterface
{
    public function getPartNumber(): string;

    public function getRevision(): string;

    public function getFileClass(): string;
}
