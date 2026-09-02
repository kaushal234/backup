<?php

declare(strict_types=1);

namespace App\ION\Serializer;

use App\ION\DataProcessor\IONDataProcessor;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

trait IONSyncSupportTrait
{
    private function supportIONSync(array $context): bool
    {
        return \in_array(IONDataProcessor::ION_SYNC, $context[AbstractNormalizer::GROUPS] ?? [], true);
    }
}
