<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\ContextProviders\PurchaseOrder;

use App\FileSystem\Zip\ContextProviders\AbstractMultipleFoldersEntityAdapterContextProvider;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;

class PurchaseOrderZipAdapterContextProvider extends AbstractMultipleFoldersEntityAdapterContextProvider
{
    public static function getClass(): string
    {
        return PurchaseOrder::class;
    }

    public function getIterablePropertyPath(): string
    {
        return 'lines';
    }

    public function getFilenamePropertyPath(): string
    {
        return 'itemCode';
    }

    /** @param PurchaseOrder $subject */
    public function getArchiveName(object $subject): string
    {
        return $subject->orderIdentifier;
    }
}
