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
    normalizationContext: ['groups' => ['responsible']],
    denormalizationContext: []
)]
#[ORM\Table]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
class Responsible
{
    /** @var string */
    final public const TLD = 'TLD';

    /** @var string */
    final public const SUPPLIER = 'Supplier';

    /** @var string */
    final public const CUSTOMER = 'Customer';

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::TLD, self::SUPPLIER, self::CUSTOMER])]
    #[Groups(['responsible'])]
    public string $name;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
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
