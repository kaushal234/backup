<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Enum\SupplySource;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\PurchasingBenchmark;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PurchasingBenchmarkDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface, ServiceSubscriberInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'BILL_OF_MATERIAL_PURCHASING_BENCHMARK_DENORMALIZER_ALREADY_CALLED';

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
        return PurchasingBenchmark::class === $type && !($context[self::ALREADY_CALLED] ?? null);
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

        $data['supplySource'] = $this->serviceLocator->get(TranslatorInterface::class)->trans('ion.enum.supply_source.'.SupplySource::from((int) $data['supplySource'])->name, [], 'ion');
        $data['purchasingBySites'] = IONXmlDecoder::enforceIndexedCollection($data['itemByOtherSites']['itemByOtherSite'] ?? []);
        $data['items'] = IONXmlDecoder::enforceIndexedCollection($data['items']['item'] ?? []);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public static function getSubscribedServices(): array
    {
        return [TranslatorInterface::class];
    }
}
