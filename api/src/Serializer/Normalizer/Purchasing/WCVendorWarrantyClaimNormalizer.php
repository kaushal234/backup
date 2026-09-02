<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Purchasing;

use ApiPlatform\Metadata\Get;
use App\Entity\AuthorizedApplication;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Purchasing\WCVendorWarrantyClaim;
use LegacyBundle\Manager\WarrantyClaimManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class WCVendorWarrantyClaimNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'WC_VENDOR_WARRANTY_CLAIM_NORMALIZER_ALREADY_CALLED';
    private readonly Security $security;
    private readonly WarrantyClaimManager $manager;

    public function __construct(Security $security, WarrantyClaimManager $manager)
    {
        $this->security = $security;
        $this->manager = $manager;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof WCVendorWarrantyClaim && ($context['operation'] ?? null) instanceof Get && (($user = $this->security->getUser()) instanceof AuthorizedApplication || $user instanceof VendorUser) && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param WCVendorWarrantyClaim $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);
        $normalizedData['warrantyClaim'] = false !== ($warrantyClaim = $this->manager->findOneById($object->warrantyClaimId)) ? $warrantyClaim : null;

        return $normalizedData;
    }
}
