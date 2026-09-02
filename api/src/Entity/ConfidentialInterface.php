<?php

declare(strict_types=1);

namespace App\Entity;

interface ConfidentialInterface
{
    public function isConfidential(): bool;
}
