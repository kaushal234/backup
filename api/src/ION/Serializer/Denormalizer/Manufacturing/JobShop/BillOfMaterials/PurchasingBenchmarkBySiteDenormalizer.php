<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\PurchasingBenchmarkBySite;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PurchasingBenchmarkBySiteDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface, ServiceSubscriberInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'BILL_OF_MATERIAL_PURCHASING_BENCHMARK_BY_SITE_DENORMALIZER_ALREADY_CALLED';

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
        return PurchasingBenchmarkBySite::class === $type && !($context[self::ALREADY_CALLED] ?? null);
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

        $data['buyer'] = IONXmlDecoder::trim($data['buyer']);
        IONXmlDecoder::renameKey($data, 'purchasePrice', 'price');
        IONXmlDecoder::renameKey($data, 'purchaseCurrency', 'currency');
        IONXmlDecoder::renameKey($data, 'leadtime', 'leadTime');
        IONXmlDecoder::renameKey($data, 'leadtimeUnit', 'leadTimeUnit');
        IONXmlDecoder::renameKey($data, 'buyFromBusinessPartner', 'mainSupplier');
        IONXmlDecoder::renameKey($data, 'itemCostingCurrency', 'costCurrency');

        $data['price'] = round((float) $data['price'], 2);
        $data['standardCost'] = round((float) $data['standardCost'], 2);

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public static function getSubscribedServices(): array
    {
        return [TranslatorInterface::class];
    }
}
