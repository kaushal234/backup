<?php

declare(strict_types=1);

namespace App\Entity\Manufacturing;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Family;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_MANUFACTURING_FAMILY_ADMIN')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_MANUFACTURING_FAMILY_ADMIN')"),
        new Delete(security: "is_granted('FEATURE_MANUFACTURING_FAMILY_ADMIN')"),
    ],
    routePrefix: 'manufacturing',
    normalizationContext: ['groups' => ['family', 'manufacturing_family']],
    denormalizationContext: ['groups' => ['manufacturing_family:write']],
)]
#[ORM\Table(name: 'manufacturing_families')]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(SearchFilter::class, properties: ['id'])]
class ManufacturingFamily extends Family
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['manufacturing_family', 'catalogue_product', 'manufacturing_family:write'])]
    protected string $name;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['manufacturing_family', 'manufacturing_family:write'])]
    private ?int $testDuration = null;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTestDuration(): ?int
    {
        return $this->testDuration;
    }

    public function setTestDuration(?int $testDuration): self
    {
        $this->testDuration = $testDuration;

        return $this;
    }
}
