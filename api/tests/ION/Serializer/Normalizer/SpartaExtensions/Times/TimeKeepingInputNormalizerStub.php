<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Normalizer\SpartaExtensions\Times;

use App\ION\Dto\SpartaExtensions\Times\TimeKeepingPostTransactionInput;
use App\ION\Serializer\Normalizer\SpartaExtensions\Times\TimeKeepingInputNormalizer;
use Cake\Chronos\Chronos;

class TimeKeepingInputNormalizerStub extends TimeKeepingInputNormalizer
{
    /** @param TimeKeepingPostTransactionInput $object */
    public function normalize($object, $format = null, array $context = []): array
    {
        Chronos::setTestNow('2022-11-23 10:10:10');

        return parent::normalize($object, $format, $context);
    }
}
