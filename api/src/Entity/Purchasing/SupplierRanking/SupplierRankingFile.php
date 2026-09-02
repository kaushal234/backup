<?php

declare(strict_types=1);

namespace App\Entity\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'supplier_rankings_files')]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('SUPPLIER_RANKING_READ_VOTER', object) or is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL')"),
        new Get(security: "is_granted('SUPPLIER_RANKING_READ_VOTER', object) or is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL')"),
    ],
    routePrefix: '/purchasing',
    normalizationContext: ['groups' => ['supplier_ranking_file', 'file_category']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'supplierRanking' => 'exact',
])]
class SupplierRankingFile extends File
{
    #[ORM\ManyToOne(targetEntity: FileCategory::class), ORM\JoinColumn(nullable: false)]
    #[Transferable(manager: 'manager.file_category')]
    #[Groups('supplier_ranking_file')]
    public FileCategory $category;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups('supplier_ranking_file')]
    public ?\DateTimeInterface $expiredAt = null;

    #[ORM\ManyToOne(targetEntity: SupplierRanking::class, inversedBy: 'files')]
    public ?SupplierRanking $supplierRanking = null;

    #[Groups('supplier_ranking_file')]
    public function isExpired(): bool
    {
        if (!$this->expiredAt instanceof \DateTimeInterface) {
            return false;
        }

        if ($this->expiredAt < new \DateTime()) {
            return true;
        }

        return false;
    }
}
