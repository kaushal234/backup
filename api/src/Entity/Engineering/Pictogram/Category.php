<?php

declare(strict_types=1);

namespace App\Entity\Engineering\Pictogram;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'pictograms_categories')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_PICTOGRAM_CATEGORY_CREATE')"),
        new Get(),
        new Delete(security: "is_granted('FEATURE_PICTOGRAM_CATEGORY_DELETE')"),
        new Put(security: "is_granted('FEATURE_PICTOGRAM_CATEGORY_UPDATE')"),
    ],
    routePrefix: 'engineering/pictogram',
    normalizationContext: ['groups' => ['pictogram_category:read']],
)]
#[ApiFilter(SimpleSearchFilter::class, properties: ['id' => 'exact', 'name' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'name' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name'])]
#[App\Loggable]
class Category
{
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    #[Groups(['pictogram_category:read'])]
    public string $name;

    #[ORM\Column(length: 7, nullable: true)]
    #[Assert\NotBlank, Assert\Length(max: 7)]
    #[Groups(['pictogram_category:read'])]
    public ?string $color = null;
    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['pictogram_category:read'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
