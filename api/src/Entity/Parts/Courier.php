<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_COURIER_ADMIN') or is_granted('MOO_SPR')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_COURIER_ADMIN') or is_granted('MOO_SPR')"),
        new Delete(security: "is_granted('FEATURE_COURIER_ADMIN') or is_granted('MOO_SPR')"),
    ],
    routePrefix: 'parts',
    normalizationContext: ['groups' => ['courier']],
    denormalizationContext: ['groups' => ['courier:write']],
)]
#[ORM\Table(name: 'couriers')]
#[ORM\UniqueConstraint(name: 'unique_name', columns: ['name'])]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
class Courier
{
    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['courier', 'courier:write'])]
    public string $name;

    #[ORM\Column(type: 'string', length: 512, nullable: true)]
    #[Assert\Url(requireTld: true)]
    #[Groups(['courier', 'courier:write'])]
    public ?string $url = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['courier', 'courier:write'])]
    public ?string $parameterName = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['courier'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
