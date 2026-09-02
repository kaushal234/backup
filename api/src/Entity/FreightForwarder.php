<?php

declare(strict_types=1);

namespace App\Entity;

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
use App\Entity\Directory\Location;
use App\ION\Validator\Constraints\MasterData\BusinessPartners\BusinessPartnerSupplier;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_FREIGHT_FORWARDER_ADMIN')"),
        new Put(security: "is_granted('FEATURE_FREIGHT_FORWARDER_ADMIN')"),
        new Delete(security: "is_granted('FEATURE_FREIGHT_FORWARDER_ADMIN')"),
        new Get(),
    ],
    normalizationContext: ['groups' => ['freight_forwarder', 'location_public']],
    denormalizationContext: ['groups' => ['freight_forwarder:write']],
)]
#[ORM\Table(name: 'freight_forwarders')]
#[ORM\UniqueConstraint(name: 'unique_suno_per_location', columns: ['supplier_number', 'location_id'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name'])]
#[ApiFilter(SearchFilter::class, properties: ['location' => 'exact'])]
#[App\Loggable]
class FreightForwarder implements SupplierEntityInterface
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['freight_forwarder'])]
    private int $id;

    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Groups(['freight_forwarder', 'freight_forwarder:write'])]
    private string $name;

    #[Assert\All([new Assert\Type(type: 'string'), new Assert\Email(mode: 'strict')])]
    #[ORM\Column(type: 'simple_array', length: 640)]
    #[Assert\Count(min: 1, max: 10)]
    #[Groups(['freight_forwarder', 'freight_forwarder:write'])]
    private array $emails = [];

    #[BusinessPartnerSupplier]
    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['freight_forwarder', 'freight_forwarder:write'])]
    private ?string $supplierNumber = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[Groups(['freight_forwarder', 'freight_forwarder:write'])]
    #[ValidLocation(factory: true)]
    private ?Location $location = null;

    #[Assert\AtLeastOneOf([new Assert\Blank(), new Assert\Length(min: 2, max: 2)])]
    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Choice(choices: ['en', 'fr', 'zh'])]
    #[Groups(['freight_forwarder', 'freight_forwarder:write'])]
    private ?string $language = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmails(): array
    {
        return $this->emails;
    }

    public function setEmails(array $emails): self
    {
        $this->emails = $emails;

        return $this;
    }

    public function getSupplierNumber(): ?string
    {
        return $this->supplierNumber;
    }

    public function setSupplierNumber(?string $supplierNumber): self
    {
        $this->supplierNumber = $supplierNumber;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if ((null === $this->getLocation() && null === $this->getSupplierNumber()) || (null !== $this->getLocation() && null !== $this->getSupplierNumber())) {
            return;
        }

        switch (true) {
            case null === $this->getSupplierNumber():
                $errorPath = 'supplierNumber';
                break;
            case null === $this->getLocation():
                $errorPath = 'location';
                break;
            default:
                $errorPath = null;
        }

        $context->buildViolation('Supplier number and location are both mandatory if one is set.')->atPath($errorPath)->addViolation();
    }

    public function getBusinessPartnerCode(): ?string
    {
        return $this->getSupplierNumber();
    }

    public function setSupplierName(string $name): ?self
    {
        return $this->setName($name);
    }
}
