<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\BusinessPartners;

use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BusinessPartnerDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    final public const ALREADY_CALLED = 'BUSINESS_PARTNER_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return BusinessPartner::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        $data['name'] = IONXmlDecoder::trim($data['name']);
        $data['text'] = $data['text']['#'] ?? '';

        if (isset($data['businessPartnerCode'])) {
            IONXmlDecoder::renameKey($data, 'businessPartnerCode', 'code');
        }

        if (empty($data['code'])) {
            return null;
        }

        $data['code'] = IONXmlDecoder::trim($data['code']);
        $data['buyFromDepartments'] = IONXmlDecoder::enforceIndexedCollection($data['buyFrom']['departments']['department'] ?? []);
        unset($data['buyFrom']);

        if (isset($data['buyer'])) {
            $data['buyer'] = [
                'fullName' => $data['buyer']['name'],
                'employeeCode' => $data['buyer']['employee'],
                'emailAddress' => $data['buyer']['email'],
            ];
        } else {
            $data['buyer'] = null;
        }

        $contacts = array_filter(IONXmlDecoder::enforceIndexedCollection($data['contacts']['contact'] ?? []), static function ($contact) {
            return '' !== IONXmlDecoder::trim($contact['emailAddress']);
        });

        $formattedContacts = [];
        foreach ($contacts as $contact) {
            $formattedContacts[] = [
                'contactCode' => $contact['code'],
                'UserArea' => ['categories' => $contact['categories']],
                'fullName' => implode(' ', [IONXmlDecoder::trim($contact['firstName']), IONXmlDecoder::trim($contact['middleName']), IONXmlDecoder::trim($contact['lastName'])]),
                'emailAddress' => $contact['emailAddress'],
                'telephone' => $contact['primaryPhone'] ?? '',
            ];
        }

        $data['contacts'] = $formattedContacts;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
