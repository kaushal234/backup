<?php

declare(strict_types=1);

namespace App\ION\Serializer\Normalizer\Manufacturing\JobShop;

interface NonConformityByItemInterface
{
    public function getPartNumber(): string;

    /**
     * @return iterable<NonConformityByItemInterface>
     */
    public function getItems(): iterable;
}
