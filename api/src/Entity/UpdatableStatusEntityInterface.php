<?php

declare(strict_types=1);

namespace App\Entity;

interface UpdatableStatusEntityInterface
{
    public function getStatus(): string;

    public function setStatus(string $status): object;
}
