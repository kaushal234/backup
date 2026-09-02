<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\ContextProviders\RequestForQuotation;

use App\FileSystem\Zip\ContextProviders\AbstractMultipleFoldersEntityAdapterContextProvider;
use App\ION\Resources\Procurement\RequestForQuotation;

class RequestForQuotationZipAdapterContextProvider extends AbstractMultipleFoldersEntityAdapterContextProvider
{
    public static function getClass(): string
    {
        return RequestForQuotation::class;
    }

    public function getIterablePropertyPath(): string
    {
        return 'lines';
    }

    public function getFilenamePropertyPath(): string
    {
        return 'item';
    }

    /** @param RequestForQuotation $subject */
    public function getArchiveName(object $subject): string
    {
        return $subject->requestForQuotationCode;
    }
}
