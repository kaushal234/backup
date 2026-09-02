<?php

declare(strict_types=1);

namespace App\ION\Dto\Manufacturing;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\ION\DataProcessor\Manufacturing\EstimatedGreenTagDateEquipmentRecordDataProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            openapi: true,
            security: "is_granted('AUTHORIZED_APPLICATION_FEATURE_ESTIMATED_GT_DATE_EQUIPMENT_RECORD')",
            output: false,
            processor: EstimatedGreenTagDateEquipmentRecordDataProcessor::class
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['equipment_record']],
    denormalizationContext: ['groups' => ['equipment_record']],
)]
class EstimatedGreenTagDateEquipmentRecord
{
    #[Groups(['equipment_record'])]
    #[Assert\NotNull]
    public string $equipmentRecord;

    #[Groups(['equipment_record'])]
    #[Assert\NotNull]
    public string $estimatedGreenTagDate;
}
