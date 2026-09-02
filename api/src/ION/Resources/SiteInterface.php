<?php

declare(strict_types=1);

namespace App\ION\Resources;

interface SiteInterface
{
    public function getSiteNumber(): ?int;
}
