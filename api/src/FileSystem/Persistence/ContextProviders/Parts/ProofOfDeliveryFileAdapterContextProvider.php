<?php

declare(strict_types=1);

namespace App\FileSystem\Persistence\ContextProviders\Parts;

use App\Entity\Parts\ProofOfDeliveryFile;
use App\FileSystem\Persistence\ContextProviders\AbstractStandardFileAdapterContextProvider;

class ProofOfDeliveryFileAdapterContextProvider extends AbstractStandardFileAdapterContextProvider
{
    public function getFileProperty(): string
    {
        return 'proofOfDelivery';
    }

    public static function getClass(): string
    {
        return ProofOfDeliveryFile::class;
    }

    public function getDirectory(): string
    {
        return 'parts/proof_of_delivery';
    }
}
