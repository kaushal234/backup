<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

interface BillOfMaterialsIdentifiersInterface
{
    public function getProject(): string;

    public function getItem(): string;

    public function getSite(): int;
}
