<?php

declare(strict_types=1);

namespace App\ION\Resources;

interface RevisionDateInterface
{
    public function getDate(): ?string;
}
