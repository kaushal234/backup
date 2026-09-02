<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\LockedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Customer Type.
 */
#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['customer_type', 'customer', 'expose_legacy']]),
        new Post(security: "is_granted('FEATURE_CUSTOMER_TYPE_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_CUSTOMER_TYPE_WRITE')"),
        new Delete(security: "is_granted('FEATURE_CUSTOMER_TYPE_WRITE')"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['customer_type_detail', 'expose_legacy']],
    denormalizationContext: ['groups' => ['customer_type_write']],
)]
#[ORM\Table(name: 'customer_types')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'exact', 'legacyId' => 'exact', 'id' => 'exact'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
#[LockedValue(value: CustomerType::MILITARY_TYPE_NAME, propertyPath: 'name')]
#[LockedValue(value: CustomerType::AGENT_TYPE_NAME, propertyPath: 'name')]
#[LockedValue(value: CustomerType::EQUIPMENT_BROKER_TYPE_NAME, propertyPath: 'name')]
#[LockedValue(value: CustomerType::DISTRIBUTOR_TYPE_NAME, propertyPath: 'name')]
#[LockedValue(value: CustomerType::GSE_TYPE_NAME, propertyPath: 'name')]
#[Legacy\Synchronize(table: 'lists')]
#[Legacy\ExtraColumn(column: 'parent_id', value: 0)]
#[Legacy\ExtraColumn(column: 'list_name', value: 'list.sales.customer.types')]
#[Legacy\ExtraColumn(column: 'list_key', value: '')]
class CustomerType implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    /**
     * @var string
     */
    final public const MILITARY_TYPE_NAME = 'Military';

    /**
     * @var string
     */
    final public const AGENT_TYPE_NAME = 'Agent';

    /**
     * @var string
     */
    final public const EQUIPMENT_BROKER_TYPE_NAME = 'Equipment broker';

    /**
     * @var string
     */
    final public const DISTRIBUTOR_TYPE_NAME = 'Distributor';

    /**
     * @var string
     */
    final public const GSE_TYPE_NAME = 'GSE parts broker';

    /**
     * @var array
     */
    final public const THIRD_PARTIES_NAMES = [self::AGENT_TYPE_NAME, self::EQUIPMENT_BROKER_TYPE_NAME, self::DISTRIBUTOR_TYPE_NAME, self::GSE_TYPE_NAME];

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['customer_type', 'customer_type_detail'])]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['customer_type', 'customer_type_detail', 'customer_type_write'])]
    #[Legacy\Column(column: 'list_item')]
    private string $name;

    /**
     * @var Collection<Customer>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\Customer', mappedBy: 'customerTypes')]
    #[Groups(['customer_type_detail'])]
    private Collection $customers;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
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
