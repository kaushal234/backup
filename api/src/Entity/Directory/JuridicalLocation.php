<?php

declare(strict_types=1);

namespace App\Entity\Directory;

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
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\AddressWithCountry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A juridical location in the company.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['juridical_location', 'expose_legacy']],
            security: "is_granted('FEATURE_JURIDICAL_LOCATION') or is_granted('FEATURE_SALES_ORDER_READ')"
        ),
        new Post(),
        new Get(),
        new Put(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['juridical_location_detail', 'expose_legacy']],
    denormalizationContext: ['groups' => ['juridical_location_write', 'address_write']],
    security: "is_granted('FEATURE_JURIDICAL_LOCATION')",
)]
#[UniqueEntity(fields: ['name'])]
#[ORM\Table(name: 'directory_juridical_location')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId', 'id', 'locations'])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'tld_juridical_locations')]
class JuridicalLocation implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['juridical_location', 'juridical_location_detail'])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(name: 'name', type: 'string', length: 250, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    #[Groups(['juridical_location', 'juridical_location_detail', 'juridical_location_write', 'location_detail', 'juridical_location:public'])]
    #[Legacy\Column(column: 'name')]
    private string $name;

    #[ApiProperty(iris: ['https://schema.org/PostalAddress'])]
    #[ORM\Embedded(class: '\App\Entity\AddressWithCountry')]
    #[Groups(['juridical_location_detail', 'juridical_location_write'])]
    #[Legacy\Column(column: 'address', options: ['embeddedFields' => ['address.street1', 'address.street2', 'address.town', 'address.postalCode', 'address.city', 'address.state', 'address.country']])]
    private AddressWithCountry $address;

    /**
     * @var Collection<Location>
     */
    #[ORM\OneToMany(mappedBy: 'juridicalLocation', targetEntity: 'App\Entity\Directory\Location')]
    private Collection $locations;

    public function __construct()
    {
        $this->locations = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection<Location>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
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
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return AddressWithCountry
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return JuridicalLocation
     */
    public function setAddress(AddressWithCountry $address)
    {
        $this->address = $address;

        return $this;
    }
}
