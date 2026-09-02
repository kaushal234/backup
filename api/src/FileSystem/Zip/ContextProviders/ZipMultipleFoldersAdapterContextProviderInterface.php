<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\ContextProviders;

interface ZipMultipleFoldersAdapterContextProviderInterface extends ZipAdapterContextProviderInterface
{
    public function getIterablePropertyPath(): string;

    public function getFilenamePropertyPath(): string;
}
