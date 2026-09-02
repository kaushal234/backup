<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Enum\SupplySource;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\PurchasingBenchmarkBySite;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\PurchasingBenchmarkItem;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PurchasingBenchmarkItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface, ServiceSubscriberInterface
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
        return PurchasingBenchmarkItem::class === $type;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (empty($data)) {
            return null;
        }

        $purchasingBenchmarkItem = new PurchasingBenchmarkItem();

        $purchasingBenchmarkItem->partNumber = $data['partNumber'];
        $purchasingBenchmarkItem->itemDescription = IONXmlDecoder::trim($data['itemDescription']);
        $purchasingBenchmarkItem->level = (int) $data['level'];
        $purchasingBenchmarkItem->position = (int) $data['position'];
        $purchasingBenchmarkItem->supplySource = $this->serviceLocator->get(TranslatorInterface::class)->trans('ion.enum.supply_source.'.SupplySource::from((int) $data['supplySource'])->name, [], 'ion');
        $purchasingBenchmarkItem->quantity = (float) $data['quantity'];

        $data['itemByOtherSites'] = IONXmlDecoder::enforceIndexedCollection($data['itemByOtherSites']['itemByOtherSite'] ?? []);
        $data['children'] = IONXmlDecoder::enforceIndexedCollection($data['children']['item'] ?? []);

        IONXmlDecoder::renameKey($data, 'children', 'items');
        foreach ($data['items'] as $item) {
            $purchasingBenchmarkItem->addItem($this->denormalizer->denormalize($item, $type, $format, $context));
        }

        IONXmlDecoder::renameKey($data, 'itemByOtherSites', 'purchasingBySites');
        foreach ($data['purchasingBySites'] as $purchasingBySite) {
            $purchasingBenchmarkItem->addPurchasingBySite($this->denormalizer->denormalize($purchasingBySite, PurchasingBenchmarkBySite::class, $format, $context));
        }

        return $purchasingBenchmarkItem;
    }

    public static function getSubscribedServices(): array
    {
        return [TranslatorInterface::class];
    }
}
