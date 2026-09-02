<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\EquipmentRecord;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class EquipmentRecordContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;
    private readonly Security $security;

    public function __construct(SerializerContextBuilderInterface $decorated, Security $security)
    {
        $this->decorated = $decorated;
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (EquipmentRecord::class !== $context['resource_class'] || $normalization) {
            return $context;
        }

        /** @var EquipmentRecord $er */
        $er = $request->attributes->get('data');

        if ($this->security->isGranted('EQUIPMENT_SERIAL_EDIT_VOTER', $er->getManufacturerLocation())) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'equipment_record:admin';
        }

        return $context;
    }
}
