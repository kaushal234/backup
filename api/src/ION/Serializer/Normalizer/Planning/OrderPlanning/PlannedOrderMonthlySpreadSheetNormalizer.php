<?php

declare(strict_types=1);

namespace App\ION\Serializer\Normalizer\Planning\OrderPlanning;

use ApiPlatform\Metadata\GetCollection;
use App\Entity\Directory\Location;
use App\ION\Resources\Planning\OrderPlanning\PlannedOrder;
use App\Repository\Directory\LocationRepository;
use App\Serializer\Encoder\XlsxEncoder;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlannedOrderMonthlySpreadSheetNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly LocationRepository $repository,
        private readonly EntityCacheHelperFactory $cacheFactory,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return PlannedOrder::class === ($context['resource_class'] ?? null) && ($context['operation'] ?? null) instanceof GetCollection && \in_array('planned_order:monthly', $context[AbstractObjectNormalizer::GROUPS], true) && XlsxEncoder::FORMAT === $format;
    }

    /**
     * @param PlannedOrder[] $objects
     */
    public function normalize($objects, ?string $format = null, array $context = []): array
    {
        $normalizedData = [];
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'erp');
        foreach ($objects as $object) {
            /** @var Location|null $location */
            $location = $locationCache->fetch(mb_substr($object->item, 0, 3));
            $key = \sprintf('%s-%s', $object->plannedFinishDate->format('Y-m'), mb_trim(mb_substr($object->item, 3)));
            $quantityKey = $this->translator->trans('fcr.fields.quantity', [], 'emails');
            if (!isset($normalizedData[$key])) {
                $normalizedData[$key] = [
                    $this->translator->trans('mom.meeting.location', [], 'emails') => null !== $location?->getName() ? $location->getName() : '',
                    $this->translator->trans('purchase_order.confirmation.fields.part_number', [], 'emails') => mb_trim(mb_substr($object->item, 3)),
                    $this->translator->trans('purchase_order.confirmation.fields.supplier_part_number', [], 'emails') => html_entity_decode($object->supplierPartNumber),
                    $this->translator->trans('first_article_qualification.fields.part_number.revision', [], 'emails') => $object->status,
                    $this->translator->trans('purchase_order.confirmation.fields.description', [], 'emails') => $object->itemDescription,
                    $this->translator->trans('mrp.fields.planned_delivery_date', [], 'emails') => null !== $object->plannedFinishDate ? $object->plannedFinishDate->format('Y-m') : null,
                    $this->translator->trans('mrp.fields.price', [], 'emails') => \sprintf('%s %s', $object->price, $object->currency),
                    $quantityKey => $object->quantity,
                ];
                continue;
            }
            $normalizedData[$key][$quantityKey] = (int) $normalizedData[$key][$quantityKey] + (int) $object->quantity;
        }

        return array_values($normalizedData);
    }
}
