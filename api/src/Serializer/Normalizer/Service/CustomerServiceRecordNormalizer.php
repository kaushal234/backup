<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Service;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CustomerServiceRecordNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'CUSTOMER_SERVICE_RECORD_NORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AbstractCustomerServiceRecord && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $reflection = new \ReflectionClass(AbstractCustomerServiceRecord::class);
        $attributes = $reflection->getAttributes('Doctrine\ORM\Mapping\DiscriminatorMap')[0];
        $normalizedData['type'] = array_search($object::class, $attributes->getArguments()[0], true);

        return $normalizedData;
    }
}
