<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string', length: 20)]
#[ORM\DiscriminatorMap(['iata' => 'IATACode', 'apt' => 'App\Entity\Common\Airport', 'rst' => 'App\Entity\Common\RailwayStation', 'bst' => 'App\Entity\Common\BusStation', 'olp' => 'App\Entity\Common\OffLinePoint', 'mar' => 'App\Entity\Common\MetropolitanArea', 'fpt' => 'App\Entity\Common\FerryPort', 'hpt' => 'App\Entity\Common\Heliport'])]
#[ApiResource(
    shortName: 'iataCode',
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['iata_code_detail', 'expose_legacy']],
    denormalizationContext: ['groups' => ['iata_code_write']]
)]
#[ORM\Table(name: 'iata_codes')]
#[ApiFilter(OrderFilter::class, properties: ['code' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId' => 'exact', 'code' => 'exact', 'cityCode3' => 'exact', 'cityName' => 'partial', 'country' => 'exact', 'type' => 'exact'])]
class IATACode implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;

    /**
     * @var string
     */
    final public const IATA = 'IATA';

    /**
     * @var string
     */
    final public const TLD = 'TLD';

    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 10)]
    #[Assert\NotBlank]
    #[Groups(['iata_code', 'iata_code_detail', 'iata_code_write', 'iata_code_restricted'])]
    #[Legacy\Column(column: 'airport_code')]
    protected string $code;

    #[ORM\Column(type: 'string', length: 60)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Assert\Type(type: 'string')]
    #[Groups(['iata_code', 'iata_code_detail'])]
    #[Legacy\Column(column: 'type')]
    private string $type;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['iata_code', 'iata_code_detail'])]
    private int $id;

    #[ORM\Column(name: 'city_code_3', type: 'string', length: 3, nullable: true)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 3)]
    #[Groups(['iata_code', 'iata_code_detail', 'iata_code_write', 'iata_code_restricted'])]
    #[Legacy\Column(column: 'city_code_3')]
    private ?string $cityCode3 = null;

    #[ORM\Column(name: 'city_name', type: 'string', length: 60, nullable: false)]
    #[ApiProperty(iris: ['https://schema.org/addressLocality'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 60)]
    #[Assert\NotBlank]
    #[Groups(['iata_code', 'iata_code_detail', 'iata_code_write', 'airport_list'])]
    #[Legacy\Column(column: 'city_name')]
    private string $cityName;

    #[ORM\Column(type: 'string', length: 60, nullable: true)]
    #[ApiProperty(iris: ['https://schema.org/addressLocality'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 60)]
    #[Groups(['iata_code', 'iata_code_detail', 'iata_code_write'])]
    #[Legacy\Column(column: 'state')]
    private ?string $state = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Country')]
    #[ORM\JoinColumn(nullable: true)]
    #[ApiProperty(iris: ['https://schema.org/addressCountry'])]
    #[Groups(['iata_code', 'iata_code_detail', 'iata_code_write'])]
    #[Legacy\Column(column: 'ctry_code_2', transformer: ObjectToProperty::class, options: ['property' => 'isoCode2'])]
    private ?Country $country = null;

    #[ORM\Column(type: 'string', length: 22, nullable: true)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 22)]
    #[Groups(['iata_code', 'iata_code_detail', 'iata_code_write', 'iata_code_restricted'])]
    #[Legacy\Column(column: 'airport_name')]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Assert\Type(type: 'string')]
    #[Assert\NotNull]
    #[Assert\Choice(choices: [self::IATA, self::TLD])]
    #[Groups(['iata_code', 'iata_code_detail', 'iata_code_write'])]
    #[Legacy\Column(column: 'source')]
    private string $source;

    #[ORM\Column(type: 'float', nullable: true)]
    #[ApiProperty(iris: ['https://schema.org/Float'])]
    #[Groups(['iata_code', 'iata_code_detail', 'iata_code_write'])]
    #[Legacy\Column(column: 'latitude')]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[ApiProperty(iris: ['https://schema.org/Float'])]
    #[Groups(['iata_code', 'iata_code_detail', 'iata_code_write'])]
    #[Legacy\Column(column: 'longitude')]
    private ?float $longitude = null;

    public function __toString()
    {
        return $this->code;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCityCode3(): ?string
    {
        return $this->cityCode3;
    }

    public function setCityCode3(?string $cityCode3): self
    {
        $this->cityCode3 = $cityCode3;

        return $this;
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function setCityName(string $cityName): self
    {
        $this->cityName = $cityName;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }
}
