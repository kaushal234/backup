<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\MasterData\Items;

use App\ION\Resources\MasterData\Items\ItemMonologistic;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ItemMonologisticDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface, ServiceSubscriberInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'ITEM_MONOLOGISTIC_DENORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly ContainerInterface $serviceLocator)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return ItemMonologistic::class === $type && !($context[self::ALREADY_CALLED] ?? null);
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

        $data['itemCode'] = str_replace('.', '_', IONXmlDecoder::trim($data['itemCode']));
        $locale = $this->serviceLocator->get(TranslatorInterface::class)->getLocale();
        $data['description'] = IONXmlDecoder::getLocalizedValue($data['description'], $locale);

        $data['unitOfMeasure'] = $data['baseUOM'] ?? null;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public static function getSubscribedServices(): array
    {
        return [TranslatorInterface::class];
    }
}
