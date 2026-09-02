<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\BusinessPartners;

use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BusinessPartnerContactDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'BUSINESS_PARTNER_CONTACT_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return BusinessPartnerContact::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['emailAddress'] = IONXmlDecoder::trim($data['emailAddress'] ?? '');
        $data['familyName'] = IONXmlDecoder::trim($data['familyName'] ?? '', false, null, false);
        $data['fullName'] = IONXmlDecoder::trim($data['fullName'] ?? '');
        $data['firstName'] = IONXmlDecoder::trim($data['firstName'] ?? '', false, null, false);
        $data['middleName'] = IONXmlDecoder::trim($data['middleName'] ?? '');
        $data['language'] = IONXmlDecoder::trim($data['language'] ?? '');
        $data['telephone'] = IONXmlDecoder::trim($data['telephone'] ?? '');

        foreach (['businessPartners' => 'businessPartner', 'categories' => 'category'] as $key => $value) {
            if (isset($data['UserArea'][$key])) {
                $data[$key] = IONXmlDecoder::enforceIndexedCollection($data['UserArea'][$key][$value] ?? []);
            }
        }

        foreach ($data['categories'] as &$category) {
            $category['description'] = IONXmlDecoder::trim($category['description']);
        }

        unset($data['UserArea']);
        if ('' !== ($data['AddressCode'] ?? '')) {
            $data['address'] = [
                'addressCode' => $data['AddressCode'],
                'addressLine1' => $data['AddressLine1'],
                'addressLine2' => $data['AddressLine2'],
                'addressLine3' => $data['AddressLine3'],
                'addressLine4' => $data['AddressLine4'],
                'addressLine5' => $data['AddressLine5'],
                'addressLine6' => $data['AddressLine6'],
            ];
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
