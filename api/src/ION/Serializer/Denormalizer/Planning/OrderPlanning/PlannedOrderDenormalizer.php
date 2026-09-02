<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Planning\OrderPlanning;

use App\ION\Resources\Planning\OrderPlanning\PlannedOrder;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PlannedOrderDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return PlannedOrder::class === $type;
    }

    /**
     * @return PlannedOrder
     *
     * @throws \Exception
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $date = new \DateTime();
        $data['plannedStartDate'] = $date->setTimestamp((int) $data['plannedStartDate'])->format('c');
        $data['plannedFinishDate'] = $date->setTimestamp((int) $data['plannedFinishDate'])->format('c');

        $plannedOrder = new PlannedOrder();
        $plannedOrder->status = $data['status'];
        $plannedOrder->plannedStartDate = new \DateTime(IONXmlDecoder::trim($data['plannedStartDate'], true));
        $plannedOrder->plannedFinishDate = new \DateTime(IONXmlDecoder::trim($data['plannedFinishDate'], true));
        $plannedOrder->buyFromBusinessPartner = IONXmlDecoder::trim($data['buyFromBusinessPartner']);
        $plannedOrder->buyFromBusinessPartnerName = IONXmlDecoder::trim($data['buyFromBusinessPartnerName']);
        $plannedOrder->item = IONXmlDecoder::trim($data['item']);
        $plannedOrder->supplierPartNumber = IONXmlDecoder::trim($data['businessPartnerItem']);
        $plannedOrder->itemDescription = IONXmlDecoder::trim($data['itemDescription']);
        $plannedOrder->quantity = $data['quantity'];
        $plannedOrder->unitOfMeasure = IONXmlDecoder::trim($data['unitOfMeasure']);
        $plannedOrder->plannedOrderIdentifier = IONXmlDecoder::trim($data['plannedOrderCode']);
        if (\array_key_exists('purchasePrice', $data)) {
            $plannedOrder->price = (null === IONXmlDecoder::trim($data['purchasePrice'], true)) ? null : (float) $data['purchasePrice'];
        }
        if (\array_key_exists('purchaseCurrency', $data)) {
            $plannedOrder->currency = (string) $data['purchaseCurrency'];
        }

        return $plannedOrder;
    }
}
