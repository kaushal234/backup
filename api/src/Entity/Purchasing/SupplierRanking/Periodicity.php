<?php

declare(strict_types=1);

namespace App\Entity\Purchasing\SupplierRanking;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes\Transferable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Periodicity of notification to request a new review of supplier ranking.
 * Depends on his classification and expertise level.
 */
#[ORM\Entity]
#[ORM\Table(name: 'supplier_rankings_periodicities')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_SUPPLIER_RANKING_ADMIN')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_SUPPLIER_RANKING_ADMIN')"),
        new Delete(security: "is_granted('FEATURE_SUPPLIER_RANKING_ADMIN')"),
    ],
    routePrefix: 'purchasing/supplier_ranking',
    normalizationContext: ['groups' => ['periodicity']],
    denormalizationContext: ['groups' => ['periodicity:write']],
)]
class Periodicity
{
    #[ORM\Id, ORM\ManyToOne(targetEntity: ExpertiseLevel::class, inversedBy: 'periodicityByClassifications')]
    #[Transferable(manager: 'manager.expertise_level')]
    #[ApiProperty(identifier: true)]
    #[Groups(['periodicity', 'periodicity:write'])]
    public ExpertiseLevel $expertiseLevel;

    #[ORM\Id, ORM\ManyToOne(targetEntity: Classification::class, inversedBy: 'periodicityByExpertiseLevels')]
    #[Transferable(manager: 'manager.classification')]
    #[ApiProperty(identifier: true)]
    #[Groups(['periodicity', 'periodicity:write'])]
    public Classification $classification;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['periodicity', 'periodicity:write'])]
    public int $months;
}
