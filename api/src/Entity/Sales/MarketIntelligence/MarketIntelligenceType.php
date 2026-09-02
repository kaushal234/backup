<?php

declare(strict_types=1);

namespace App\Entity\Sales\MarketIntelligence;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_MARKET_INTELLIGENCE_TYPE_WRITE') or is_granted('MOO_MIM')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_MARKET_INTELLIGENCE_TYPE_WRITE') or is_granted('MOO_MIM')"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['market_intelligence_type']],
    denormalizationContext: ['groups' => ['market_intelligence_type:write']],
)]
#[ORM\Table]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[App\Loggable]
class MarketIntelligenceType
{
    /**
     * @var string
     */
    final public const MISCELLANEOUS_INFORMATION = 'Miscellaneous Information';

    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['market_intelligence_type', 'market_intelligence_type:write'])]
    public string $name;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['market_intelligence_type'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
