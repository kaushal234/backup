<?php

declare(strict_types=1);

namespace App\FileSystem\Zip;

use App\FileSystem\AdapterFactory;

class ZipManagerFactory
{
    public function __construct(
        private readonly AdapterFactory $adapterFactory
    ) {
    }

    public function getManagerForClass(string $class): ZipManager
    {
        /** @var ZipAdapter $adapter */
        $adapter = $this->adapterFactory->getAdapterForClass(ZipAdapter::class, $class);

        return new ZipManager($adapter);
    }
}
