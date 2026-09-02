<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Warehousing;

use App\FileSystem\Image\VaultPartImageFileProvider;
use App\ION\Resources\Warehousing\Inventory;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class InventoryDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface, ServiceSubscriberInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'INVENTORY_DENORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly ContainerInterface $serviceLocator)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return Inventory::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $vault = $this->serviceLocator->get(VaultPartImageFileProvider::class);

        $context[self::ALREADY_CALLED] = true;
        $data['item'] = IONXmlDecoder::trim($data['item']);
        $data['productLine'] = IONXmlDecoder::trim($data['productLine']);
        IONXmlDecoder::renameKey($data, 'itemDescription', 'description');
        $data['description'] = IONXmlDecoder::trim($data['description']);
        $data['unitOfMeasure'] = IONXmlDecoder::trim($data['unitOfMeasure']);
        $data['pmoc'] = IONXmlDecoder::trim($data['pmoc']);
        IONXmlDecoder::renameKey($data, 'itemBySite', 'siteItems');
        $data['siteItems'] = IONXmlDecoder::enforceIndexedCollection($data['siteItems']);
        $data['priceBookLines'] = IONXmlDecoder::enforceIndexedCollection($data['priceBookLines']['mip'] ?? []);

        $data['pictures'] = [];
        foreach ($vault->getAll($data['item']) as $index => $file) {
            $data['pictures'][] = [
                'key' => $index + 1,
                'uri' => $this->serviceLocator->get(UrlGeneratorInterface::class)->generate('manufacturing_inventory_image', [
                    'item' => $data['item'],
                    'index' => $index + 1,
                ]),
                'filename' => $file->getFilename(),
                'extension' => $file->getExtension(),
                'size' => $file->getSize(),
            ];
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public static function getSubscribedServices(): array
    {
        return [
            VaultPartImageFileProvider::class,
            UrlGeneratorInterface::class,
        ];
    }
}
