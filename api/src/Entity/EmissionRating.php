<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Link\Formatter\FormatterLinkEmissionRatingName;
use App\Link\Mapping\Attributes\LinkField;
use App\Link\Resource\LinkResourceInterface;
use App\Link\Resource\LinkResourceTrait;
use App\Validator\Constraints\LockedValue;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\BooleanToChar;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['emission_rating', 'expose_legacy']]),
        new Post(
            denormalizationContext: ['groups' => ['emission_rating:create']],
            security: "is_granted('FEATURE_EMISSION_RATING_WRITE')",
        ),
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['emission_rating:update']],
            security: "is_granted('FEATURE_EMISSION_RATING_WRITE')",
        ),
    ],
    normalizationContext: ['groups' => ['emission_rating:detail', 'expose_legacy']],
)]
#[ORM\Table(name: 'emission_ratings')]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'ASC', 'name' => 'ASC'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'whitelist' => [EmissionRating::SHOW_OBSOLETE]])]
#[LockedValue(value: EmissionRating::INTELLIGENT_BATTERY_SYSTEM, propertyPath: 'name')]
#[LockedValue(value: EmissionRating::HYBRID_SYSTEM, propertyPath: 'name')]
#[Legacy\Synchronize(table: 'lists')]
#[Legacy\ExtraColumn(column: 'parent_id', value: 0)]
#[Legacy\ExtraColumn(column: 'list_key', value: '')]
class EmissionRating implements \Stringable, LegacyIdInterface, LinkResourceInterface
{
    use LegacyIdentifierTrait;
    use LinkResourceTrait;

    /** @var string */
    final public const SHOW_OBSOLETE = 'show_obsolete';

    /** @var string */
    final public const INTELLIGENT_BATTERY_SYSTEM = 'iBS';

    /** @var string */
    public const HYBRID_SYSTEM = 'ipHS + iBS';

    /** @var array */
    public const INTELLIGENT_BATTERY_SYSTEMS = [self::INTELLIGENT_BATTERY_SYSTEM, self::HYBRID_SYSTEM];

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['emission_rating', 'emission_rating:detail'])]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['emission_rating', 'emission_rating:detail', 'emission_rating:create', 'sfr_export', 'demo_detail', 'odp:view'])]
    #[Legacy\Column(column: 'list_item')]
    #[LinkField(fields: ['name'], transformer: FormatterLinkEmissionRatingName::class)]
    private string $name;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['emission_rating:detail', 'emission_rating:update'])]
    #[Legacy\Column(column: 'list_name', transformer: BooleanToChar::class, options: ['trueValue' => 'list.engine.tiers_obsolete', 'falseValue' => 'list.engine.tiers'])]
    private bool $obsolete = false;

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setObsolete(bool $obsolete): self
    {
        $this->obsolete = $obsolete;

        return $this;
    }

    public function isObsolete(): bool
    {
        return $this->obsolete;
    }
}
