<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\ContextProviders\NonConformity;

use App\Entity\Quality\NonConformity;
use App\FileSystem\Zip\ContextProviders\AbstractZippableEntityAdapterContextProvider;

class NonConformityZipAdapterContextProvider extends AbstractZippableEntityAdapterContextProvider
{
    public static function getClass(): string
    {
        return NonConformity::class;
    }

    /** @param NonConformity $subject */
    public function getArchiveName(object $subject): string
    {
        return \sprintf('NCR-%d', $subject->getId());
    }
}
