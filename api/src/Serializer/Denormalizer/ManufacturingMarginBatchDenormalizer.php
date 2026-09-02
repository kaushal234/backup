<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Dto\Finance\ManufacturingMarginBatch;
use App\Entity\EquipmentRecord;
use App\Entity\Finance\Currency;
use App\Entity\Manufacturing\ProductManufacturing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ManufacturingMarginBatchDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'MANUFACTURING_MARGIN_BATCH_ALREADY_CALLED';
    private readonly EntityManagerInterface $entityManager;
    private readonly IriConverterInterface $iriConverter;
    private readonly CacheInterface $cache;

    public function __construct(EntityManagerInterface $entityManager, IriConverterInterface $iriConverter, CacheInterface $arrayCache)
    {
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
        $this->cache = $arrayCache;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;
        $equipmentRecordRepository = $this->entityManager->getRepository(EquipmentRecord::class);
        $currencyRepository = $this->entityManager->getRepository(Currency::class);

        foreach ($data['margins'] as &$manufacturingMarginArray) {
            /** @var EquipmentRecord|null $equipmentRecord */
            $equipmentRecord = $equipmentRecordRepository->findOneBy(['legacyId' => $manufacturingMarginArray['equipmentRecord']]);
            $manufacturingMarginArray['equipmentRecord'] = null !== $equipmentRecord ? $this->iriConverter->getIriFromResource($equipmentRecord) : null;

            $manufacturingMarginArray['currency'] = $this->cache->get($manufacturingMarginArray['currency'], function (ItemInterface $item) use ($currencyRepository, $manufacturingMarginArray) {
                /** @var Currency|null $currency */
                $currency = $currencyRepository->findOneBy(['name' => $manufacturingMarginArray['currency']]);

                return null !== $currency ? $this->iriConverter->getIriFromResource($currency) : null;
            });

            $manufacturingMarginArray['exportedAt'] = \sprintf('%s-%s', (string) $manufacturingMarginArray['year'], (string) $manufacturingMarginArray['month']);
            unset($manufacturingMarginArray['year'], $manufacturingMarginArray['month']);

            $repository = $this->entityManager->getRepository(ProductManufacturing::class);
            if (null !== $equipmentRecord) {
                $factory = $equipmentRecord->getManufacturerLocation();
                $product = $equipmentRecord->getProduct();
                if (null !== $factory && null !== $product) {
                    $effectiveAt = (new \DateTime($manufacturingMarginArray['exportedAt']))
                        ->setDate(
                            (int) (new \DateTime($manufacturingMarginArray['exportedAt']))->format('Y'),
                            1,
                            1
                        )
                        ->setTime(0, 0);
                    $productManufacturing = $repository->findOneBy(['product' => $product, 'factory' => $factory, 'effectiveAt' => $effectiveAt]);
                    if (!$productManufacturing instanceof ProductManufacturing || null === $productManufacturing->getModelBaseHours()) {
                        continue;
                    }

                    $manufacturingMarginArray['optionConfigurationParameterHours'] = $manufacturingMarginArray['standardHours'] - ($productManufacturing->getModelBaseHours() * $productManufacturing->getIndustrialIncorporationParameter() / 100);
                }
            }
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return ManufacturingMarginBatch::class === $type && !($context[self::ALREADY_CALLED] ?? null);
    }
}
