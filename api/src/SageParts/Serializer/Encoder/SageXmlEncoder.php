<?php

declare(strict_types=1);

namespace App\SageParts\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

class SageXmlEncoder extends XmlEncoder
{
    final public const FORMAT = 'sage_xml';

    /**
     * @return array|mixed|string
     */
    public function decode(string $data, string $format, array $context = []): mixed
    {
        if (empty($data)) {
            return [];
        }

        $decodedData = parent::decode($data, XmlEncoder::FORMAT, $context);
        $decodedData = $decodedData['soap:Body']['PriceAndAvailabilityRequestResponse']['PriceAndAvailabilityRequestResult'];
        $decodedData = parent::decode($decodedData, $format, $context);
        $decodedData = $decodedData['Item'];

        if (empty($decodedData)) {
            return [];
        }

        return $decodedData;
    }

    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}
