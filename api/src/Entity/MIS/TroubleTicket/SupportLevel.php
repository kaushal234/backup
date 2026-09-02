<?php

declare(strict_types=1);

namespace App\Entity\MIS\TroubleTicket;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'trouble_ticket_support_level')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => ['support_level', 'support_level:detail']],
    denormalizationContext: ['groups' => []],
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'level', 'name'])]
#[ApiFilter(SearchFilter::class, properties: ['level', 'name'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id',
    'level',
    'name' => 'partial',
    'description' => 'partial',
])]
class SupportLevel implements \Stringable
{
    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Groups(['support_level'])]
    public int $level;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['support_level'])]
    public string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['support_level:detail'])]
    public ?string $description = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['support_level'])]
    private int $id;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
