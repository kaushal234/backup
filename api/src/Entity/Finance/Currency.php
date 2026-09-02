<?php

declare(strict_types=1);

namespace App\Entity\Finance;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_CURRENCY_WRITE')"),
        new Get(),
    ],
    routePrefix: 'finance',
    normalizationContext: ['groups' => ['currency', 'expose_legacy']],
    order: ['name' => 'asc'],
)]
#[ORM\Table(name: 'currencies')]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'exact',
    'legacyId' => 'exact',
    'name' => 'partial',
])]
#[ApiFilter(SearchFilter::class, properties: ['name', 'legacyId'])]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[Legacy\Synchronize(table: 'lists')]
#[Legacy\ExtraColumn(column: 'parent_id', value: 0)]
#[Legacy\ExtraColumn(column: 'list_name', value: 'list.common.currency')]
#[Legacy\ExtraColumn(column: 'list_key', value: '')]
class Currency implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['currency', 'location_detail'])]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 3, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(min: 3, max: 3)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['currency', 'location_detail', 'user:me', 'people_detail'])]
    #[Legacy\Column(column: 'list_item')]
    private string $name;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
