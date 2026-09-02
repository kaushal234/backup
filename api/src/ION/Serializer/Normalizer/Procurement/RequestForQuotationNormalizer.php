<?php

declare(strict_types=1);

namespace App\ION\Serializer\Normalizer\Procurement;

use App\Entity\Purchasing\VendorUser;
use App\ION\Resources\Procurement\RequestForQuotation;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RequestForQuotationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'REQUEST_FOR_QUOTATION_NORMALIZER_ALREADY_CALLED';

    private readonly Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof RequestForQuotation && !($context[self::ALREADY_CALLED] ?? null);
    }

    /** @param RequestForQuotation $object */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $normalizedData['businessPartnerCodes'] = [];

        $user = $this->security->getUser();

        if (!$user instanceof VendorUser) {
            return $normalizedData;
        }

        foreach ($user->contact->getBusinessPartners() as $businessPartner) {
            foreach ($object->getBidders() as $bidder) {
                if ($businessPartner->code === $bidder->bidderCode) {
                    $normalizedData['businessPartnerCodes'][] = $bidder->bidderCode;
                }
            }
        }

        return $normalizedData;
    }
}
