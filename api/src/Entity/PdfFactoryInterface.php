<?php

declare(strict_types=1);

namespace App\Entity;

interface PdfFactoryInterface
{
    public function getFileName(): string;
}
