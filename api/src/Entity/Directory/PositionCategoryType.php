<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ['name'])]
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('POSITION_CATEGORY_WRITE_VOTER')"),
        new Get(),
        new Put(security: "is_granted('POSITION_CATEGORY_WRITE_VOTER')"),
        new Delete(security: "is_granted('POSITION_CATEGORY_WRITE_VOTER')"),
    ],
    normalizationContext: ['groups' => ['position_category_type']],
    denormalizationContext: ['groups' => ['position_category_type:write']],
)]
#[ORM\Table(name: 'directory_position_category_type')]
#[ORM\UniqueConstraint(name: 'unique_position_category_type_name', columns: ['name'])]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[App\Loggable]
class PositionCategoryType
{
    #[ORM\Column(type: 'string')]
    #[Groups(['position_category_type', 'position_category_type:write'])]
    #[Assert\NotBlank]
    public string $name;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['position_category_type'])]
    private int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
