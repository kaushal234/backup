<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'support',
    normalizationContext: ['groups' => ['manual_document_category']])]
#[ORM\Table(name: 'manual_document_categories')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'exact'])]
class ManualDocumentCategory
{
    #[ORM\Column(type: 'string')]
    #[Groups(['manual_document_category', 'manual_part:recommended_spare_part_list'])]
    public string $name;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['manual_document_category'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
