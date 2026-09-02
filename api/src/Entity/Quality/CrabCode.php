<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table]
#[ApiFilter(SearchFilter::class, properties: ['code' => 'exact'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['code' => 'partial', 'description' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['code' => 'ASC'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'quality',
    normalizationContext: ['groups' => ['crab_code']],
    security: "is_granted('ACCESS_PEOPLE')",
)]
class CrabCode
{
    /** @var string */
    final public const FAI = 'FAQ  (First Article Qualification)';

    #[ORM\Column(type: 'integer')]
    #[Groups('crab_code')]
    public int $code;

    #[ORM\Column(type: 'text')]
    #[Groups('crab_code')]
    public string $description;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups('crab_code')]
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
