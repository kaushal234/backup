<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\ContextProviders\Customer;

use App\Entity\Sales\Customer;
use App\FileSystem\Zip\ContextProviders\AbstractZippableEntityAdapterContextProvider;

class CustomerZipAdapterContextProvider extends AbstractZippableEntityAdapterContextProvider
{
    public static function getClass(): string
    {
        return Customer::class;
    }

    /** @param Customer $subject */
    public function getArchiveName(object $subject): string
    {
        return \sprintf('customer-%d', $subject->getId());
    }
}
