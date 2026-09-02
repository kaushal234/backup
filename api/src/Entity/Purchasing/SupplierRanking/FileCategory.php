<?php

declare(strict_types=1);

namespace App\Entity\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Purchasing\FileCategoryTransferController;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'supplier_rankings_file_categories')]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('FEATURE_SUPPLIER_RANKING_READ') or is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL')"),
        new Post(),
        new Get(security: "is_granted('FEATURE_SUPPLIER_RANKING_READ') or is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL')"),
        new Delete(),
        new Put(
            uriTemplate: '/file_categories/{id}/transfer',
            controller: FileCategoryTransferController::class,
            deserialize: false,
            name: 'transfer_file_category',
        ),
        new Put(),
    ],
    routePrefix: '/purchasing/supplier_ranking',
    normalizationContext: ['groups' => ['file_category']],
    denormalizationContext: ['groups' => ['file_category:write']],
    order: ['name'],
    security: "is_granted('FEATURE_SUPPLIER_RANKING_ADMIN')",
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name'])]
class FileCategory
{
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 100)]
    #[Groups(['file_category', 'file_category:write'])]
    public string $name;

    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups('file_category')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
