<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Validator\Constraints\LockedValue;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['code'])]
#[ApiResource(
    operations: [new GetCollection(), new Get()],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['incoterm']]
)]
#[ApiFilter(OrderFilter::class, properties: ['code' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: [
    'code',
])]
#[LockedValue(value: Incoterm::EXW, propertyPath: 'code')]
#[LockedValue(value: Incoterm::FCA, propertyPath: 'code')]
class Incoterm
{
    public const EXW = 'EXW';
    public const FCA = 'FCA';

    #[ORM\Column(type: 'string')]
    #[Groups(['incoterm'])]
    #[Assert\NotBlank]
    public string $description;

    #[ORM\Column(type: 'string')]
    #[Groups(['incoterm', 'odp:view'])]
    public string $code;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
