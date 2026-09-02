<?php

declare(strict_types=1);

namespace App\Sdk;

interface PageInterface
{
    public function getLastPage(): int;

    public function getPreviousPage(): ?int;

    public function getNextPage(): ?int;
}
