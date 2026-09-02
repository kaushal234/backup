<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\DataProcessor\Parts\ToggleArchiveDeliveryAddressProcessor;
use App\Entity\AddressWithCountry;
use App\Entity\Common\Airport;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Filter\SimpleSearchFilter;
use App\Repository\Parts\SparePartsDeliveryAddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SparePartsDeliveryAddressRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['delivery_address', 'address', 'user', 'extranet_user', 'user_profile', 'airport_list', 'customer_list', 'location_public']]),
        new Get(),
        new Patch(
            uriTemplate: '/spare_parts_request_delivery_addresses/{id}/toggle_archive',
            security: "is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL') or is_granted('MOO_SPR') or is_granted('FEATURE_SPARE_PARTS_REQUESTS_SHIPPED_TO_CLOSE')",
            deserialize: false,
            validate: false,
            name: 'toggle_archive_delivery_address',
            processor: ToggleArchiveDeliveryAddressProcessor::class,
        ),
    ],
    routePrefix: 'parts',
    normalizationContext: ['groups' => ['delivery_address', 'delivery_address:detail', 'address', 'user', 'extranet_user', 'user_profile', 'customer_list', 'location_public']],
    denormalizationContext: []
)]
#[ORM\Table(name: 'spare_parts_requests_delivery_addresses')]
#[ApiFilter(SearchFilter::class, properties: ['contact.extranetUserProfile.customer', 'airport', 'contact', 'archived', 'address.country' => 'exact', 'address.city' => 'partial', 'address.town' => 'partial'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['address.street1' => 'partial', 'address.street2' => 'partial', 'address.postalCode' => 'partial', 'address.city' => 'partial', 'address.town' => 'partial', 'address.state' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['lastUsedAt', 'firstname', 'lastname', 'airport.code', 'contact.lastname'])]
class SparePartsRequestDeliveryAddress
{
    final public const COMPANY_VALIDATION_GROUP = 'MandatoryCompany';

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ExtranetUser')]
    #[Groups(['delivery_address', 'spare_parts_request:detail', 'spare_parts_request:create', 'spare_parts_request:edit:full'])]
    public ?ExtranetUser $contact = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['delivery_address', 'spare_parts_request:detail', 'spare_parts_request:create', 'spare_parts_request:edit:full'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $firstname;

    #[ORM\Column(type: 'string')]
    #[Groups(['delivery_address', 'spare_parts_request:detail', 'spare_parts_request:create', 'spare_parts_request:edit:full'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $lastname;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['delivery_address', 'spare_parts_request:detail', 'spare_parts_request:create', 'spare_parts_request:edit:full'])]
    #[Assert\Length(max: 255, groups: ['ValidAddress'])]
    #[Assert\NotNull(groups: [self::COMPANY_VALIDATION_GROUP])]
    public ?string $company = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['delivery_address', 'spare_parts_request:detail', 'spare_parts_request:create', 'spare_parts_request:edit:full'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $phone;

    #[ApiProperty(iris: ['https://schema.org/PostalAddress'])]
    #[ORM\Embedded(class: 'App\Entity\AddressWithCountry')]
    #[Assert\Valid]
    #[Groups(['delivery_address', 'spare_parts_request:detail', 'spare_parts_request:create', 'spare_parts_request:edit:full'])]
    public AddressWithCountry $address;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Common\Airport')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['delivery_address', 'spare_parts_request:detail', 'spare_parts_request:create', 'spare_parts_request:edit:full'])]
    public ?Airport $airport = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['delivery_address'])]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['delivery_address'])]
    public ?\DateTime $lastUsedAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['delivery_address'])]
    #[Gedmo\Blameable(on: 'create')]
    public People $poster;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['delivery_address'])]
    public bool $archived = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: 'archived')]
    public ?\DateTimeInterface $archivedAt = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn]
    #[Gedmo\Blameable(on: 'change', field: 'archived')]
    public ?People $archivedBy = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['delivery_address', 'spare_parts_request:detail'])]
    private int $id;

    /**
     * @var Collection<SparePartsRequest>
     */
    #[ORM\OneToMany(mappedBy: 'deliveryAddress', targetEntity: 'App\Entity\Parts\SparePartsRequest')]
    private Collection $sparePartsRequests;

    public function __construct()
    {
        $this->address = new AddressWithCountry();
        $this->sparePartsRequests = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<SparePartsRequest>
     */
    public function getSparePartsRequests(): Collection
    {
        return $this->sparePartsRequests;
    }

    public function addSparePartsRequest(SparePartsRequest $sparePartsRequest): self
    {
        if (!$this->sparePartsRequests->contains($sparePartsRequest)) {
            $this->sparePartsRequests->add($sparePartsRequest);
        }

        return $this;
    }

    public function removeSparePartsRequest(SparePartsRequest $sparePartsRequest): self
    {
        if ($this->sparePartsRequests->contains($sparePartsRequest)) {
            $this->sparePartsRequests->removeElement($sparePartsRequest);
        }

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if ('' === (string) $this->address->getStreet1()) {
            $context->buildViolation("Address street1 can't be blank.")
                ->atPath('address.street1')
                ->addViolation();
        }

        if ('' === (string) $this->address->getPostalCode()) {
            $context->buildViolation("Address postal code can't be blank.")
                ->atPath('address.street1')
                ->addViolation();
        }

        if ('' === (string) $this->address->getCity()) {
            $context->buildViolation("Address postal code can't be blank.")
                ->atPath('address.street1')
                ->addViolation();
        }

        if ('' === (string) $this->address->getCountry()) {
            $context->buildViolation("Address postal code can't be blank.")
                ->atPath('address.street1')
                ->addViolation();
        }
    }
}
