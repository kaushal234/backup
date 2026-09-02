<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\Items;

use App\ION\Resources\MasterData\Items\Item;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'ITEM_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Item::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;
        if (empty($data)) {
            return null;
        }
        foreach (['site', 'item', 'itemDescription', 'unitOfMeasure', 'project'] as $key) {
            $data[$key] = IONXmlDecoder::trim($data[$key]);
        }

        foreach (['buyer' => ['code', 'emailAddress', 'name'], 'planner' => ['code', 'emailAddress', 'name'], 'buyFromBusinessPartner' => ['code', 'name']] as $key => $values) {
            foreach ($values as $value) {
                if (!isset($data[$key])) {
                    continue;
                }
                $data[$key][$value] = IONXmlDecoder::trim($data[$key][$value]);
            }
        }

        IONXmlDecoder::renameKey($data, 'buyFromBusinessPartner', 'businessPartner');
        IONXmlDecoder::renameKey($data, 'engineeringItemRevision', 'revision');
        IONXmlDecoder::renameKey($data, 'supplyTime', 'orderLeadTimeInWorkingDays');
        IONXmlDecoder::renameKey($data, 'estimatedStandardCost', 'standardPrice');
        IONXmlDecoder::renameKey($data, 'purchaseCurrency', 'currency');

        foreach (['planner', 'buyer'] as $key) {
            if (!isset($data[$key])) {
                continue;
            }

            IONXmlDecoder::renameKey($data[$key], 'code', 'employeeCode');
            IONXmlDecoder::renameKey($data[$key], 'name', 'fullName');
        }

        $data['lastSuppliers'] = IONXmlDecoder::enforceIndexedCollection($data['lastSuppliers']['buyFromBusinessPartner'] ?? []);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }
}
