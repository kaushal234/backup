<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Enum\LeadTimeUnit;
use App\ION\Enum\SupplySource;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\PurchasingViewItem;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PurchasingViewItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface, ServiceSubscriberInterface
{
    use DenormalizerAwareTrait;

    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return PurchasingViewItem::class === $type;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (empty($data)) {
            return null;
        }

        $item = new PurchasingViewItem();

        $item->level = (int) $data['level'];
        $item->partNumber = IONXmlDecoder::trim($data['partNumber']);
        $item->position = (int) $data['position'];
        $item->itemDescription = IONXmlDecoder::trim($data['itemDescription']);
        $item->itemOtherDescription = IONXmlDecoder::trim($data['itemOtherDescription']);
        $item->quantity = (float) $data['quantity'];
        $item->productQuantity = (float) $data['pbomQuantity'];
        $item->unitOfMeasure = IONXmlDecoder::trim($data['unitOfMeasure']);
        $item->engineeringRevision = IONXmlDecoder::trim($data['engineeringRevision']);
        $item->engineeringRevisionEffectiveDate = $data['engineeringRevisionEffectiveDate'];
        $item->engineeringRevisionExpiryDate = $data['engineeringRevisionExpiryDate'];
        if (null !== SupplySource::tryFrom((int) $data['supplySource'])) {
            $item->supplySource = $this->serviceLocator->get(TranslatorInterface::class)->trans('ion.enum.supply_source.'.SupplySource::from((int) $data['supplySource'])->name, [], 'ion');
        }
        $item->supplier = $data['supplier'];
        $item->supplierName = $data['supplierName'];
        $item->leadTime = (int) $data['leadTime'];
        if (null !== LeadTimeUnit::tryFrom((int) $data['leadTimeUnit'])) {
            $item->leadTimeUnit = $this->serviceLocator->get(TranslatorInterface::class)->trans('ion.enum.lead_time_unit.'.LeadTimeUnit::from((int) $data['leadTimeUnit'])->name, [], 'ion');
        }
        $item->inventoryOnHand = (int) $data['inventoryOnHand'];
        $item->inventoryOnOrder = (int) $data['inventoryOnOrder'];
        $item->allocated = (int) $data['allocated'];

        $data['items'] = IONXmlDecoder::enforceIndexedCollection($data['children']['item'] ?? []);
        foreach ($data['items'] as $child) {
            $item->addItem($this->denormalizer->denormalize($child, PurchasingViewItem::class, $format, $context));
        }

        return $item;
    }

    public static function getSubscribedServices(): array
    {
        return [TranslatorInterface::class];
    }
}
