<?php

declare(strict_types=1);

namespace App\ION\Serializer\Normalizer\Planning\OrderPlanning;

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

class PlannedOrderSpreadSheetNormalizer implements NormalizerInterface, NormalizerAwareInterface
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
        return $data instanceof PlannedOrder && !\in_array('planned_order:monthly', $context[AbstractObjectNormalizer::GROUPS], true) && XlsxEncoder::FORMAT === $format;
    }

    /**
     * @param PlannedOrder $object
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $locationCache = $this->cacheFactory->createEntityCache(Location::class, 'erp');
        /** @var Location|null $location */
        $location = $locationCache->fetch(mb_substr($object->item, 0, 3));

        return [
            $this->translator->trans('mom.meeting.location', [], 'emails') => null !== $location?->getName() ? $location->getName() : '',
            $this->translator->trans('purchase_order.confirmation.fields.part_number', [], 'emails') => mb_trim(mb_substr($object->item, 3)),
            $this->translator->trans('purchase_order.confirmation.fields.supplier_part_number', [], 'emails') => html_entity_decode($object->supplierPartNumber),
            $this->translator->trans('first_article_qualification.fields.part_number.revision', [], 'emails') => $object->status,
            $this->translator->trans('purchase_order.confirmation.fields.description', [], 'emails') => $object->itemDescription,
            $this->translator->trans('fcr.fields.quantity', [], 'emails') => $object->quantity,
            $this->translator->trans('mrp.fields.planned_order_date', [], 'emails') => null !== $object->plannedStartDate ? $object->plannedStartDate->format('Y-m-d') : null,
            $this->translator->trans('mrp.fields.planned_delivery_date', [], 'emails') => null !== $object->plannedFinishDate ? $object->plannedFinishDate->format('Y-m-d') : null,
        ];
    }
}
