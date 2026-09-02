<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Purchasing;

use App\Entity\Purchasing\VendorUser;
use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use App\Serializer\Normalizer\UserNormalizer;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class VendorUserNormalizer extends UserNormalizer
{
    /**
     * @var string
     */
    private const ALREADY_CALLED = 'VENDOR_USER_NORMALIZER_ALREADY_CALLED';

    public function supportsNormalization($data, $format = null, $context = []): bool
    {
        return $data instanceof VendorUser && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param VendorUser $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if (\in_array(PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP, $context[AbstractNormalizer::GROUPS], true) || \in_array('user:me', $context[AbstractObjectNormalizer::GROUPS], true)) {
            $normalizedData['roles'] = $this->extractRoles($object, $context);
        }

        return $normalizedData;
    }
}
