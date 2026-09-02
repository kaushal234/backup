<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'quality',
    normalizationContext: ['groups' => ['process']],
    denormalizationContext: []
)]
#[ORM\Table]
#[ApiFilter(OrderFilter::class, properties: ['category' => 'ASC'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['category' => 'partial'])]
class Process
{
    final public const ENGINEERING = 'ENGINEERING';
    final public const PRODUCTION = 'PRODUCTION';
    final public const PURCHASING = 'PURCHASING';
    final public const WAREHOUSE = 'WAREHOUSE';
    final public const OTHERS = 'OTHERS';

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::ENGINEERING, self::PRODUCTION, self::PURCHASING, self::WAREHOUSE, self::OTHERS])]
    #[Groups(['process'])]
    public string $category;

    #[ORM\Column(type: 'string')]
    #[Groups(['process'])]
    public string $description;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function __toString(): string
    {
        return \sprintf('%s - %s', $this->category, $this->description);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
