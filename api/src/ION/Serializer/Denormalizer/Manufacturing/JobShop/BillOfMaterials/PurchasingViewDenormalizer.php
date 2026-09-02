<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Enum\LeadTimeUnit;
use App\ION\Enum\SupplySource;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\PurchasingView;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PurchasingViewDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface, ServiceSubscriberInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'BILL_OF_MATERIAL_PURCHASING_VIEW_DENORMALIZER_ALREADY_CALLED';

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
        return PurchasingView::class === $type && !($context[self::ALREADY_CALLED] ?? null);
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

        $data['project'] = IONXmlDecoder::trim($data['project']);
        $data['product'] = IONXmlDecoder::trim($data['product']);
        $data['itemDescription'] = IONXmlDecoder::trim($data['itemDescription']);
        $data['itemOtherDescription'] = IONXmlDecoder::trim($data['itemOtherDescription']);
        $data['items'] = IONXmlDecoder::enforceIndexedCollection($data['items']['item'] ?? []);
        $data['supplySource'] = 0 === (int) $data['supplySource'] ? null : $this->serviceLocator->get(TranslatorInterface::class)->trans('ion.enum.supply_source.'.SupplySource::from((int) $data['supplySource'])->name, [], 'ion');
        $data['leadTimeUnit'] = 0 === (int) $data['leadTimeUnit'] ? null : $this->serviceLocator->get(TranslatorInterface::class)->trans('ion.enum.lead_time_unit.'.LeadTimeUnit::from((int) $data['leadTimeUnit'])->name, [], 'ion');

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public static function getSubscribedServices(): array
    {
        return [TranslatorInterface::class];
    }
}
