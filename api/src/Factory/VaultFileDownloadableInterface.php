<?php

declare(strict_types=1);

namespace App\Factory;

interface VaultFileDownloadableInterface
{
    public function getPartNumber(): string;

    public function getRevision(): string;

    public function getSite(): int;

    public function getSignalCode(): string;

    public function getDrawing(): ?string;
}
