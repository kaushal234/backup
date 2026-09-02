<?php

declare(strict_types=1);

namespace App\Entity\Sales;

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
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\Sales\CustomerRelationshipTeamRepository')]
#[UniqueEntity(
    fields: ['erpLocation', 'customer', 'customerBusinessPartnerCode'],
    message: 'This combination eCustomer + Business Partner Code + ERP Location is already used by another CRT'
)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['customer_relationship_team', 'people_public', 'expose_legacy', 'location_public']]),
        new Post(security: "is_granted('FEATURE_CUSTOMER_RELATIONSHIP_TEAM_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_CUSTOMER_RELATIONSHIP_TEAM_WRITE')"),
        new Delete(security: "is_granted('FEATURE_CUSTOMER_RELATIONSHIP_TEAM_WRITE')"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['customer_relationship_team_detail', 'people_public', 'expose_legacy', 'location_public', 'people_photo', 'file:light']],
    denormalizationContext: ['groups' => ['customer_relationship_team_write']],
)]
#[ORM\Table(name: 'customer_relationship_teams')]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC', 'customerBusinessPartnerCode' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: [
    'customer' => 'exact',
    'legacyId' => 'exact',
    'partsRepresentative' => 'exact',
    'salesRepresentative' => 'exact',
    'serviceRepresentative' => 'exact',
    'partsLocation' => 'exact',
    'serviceLocation' => 'exact',
    'erpLocation' => 'exact',
    'partsLocation.capability.sparePartsHub' => 'exact',
])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['customerBusinessPartnerCode' => 'partial', 'customer.name' => 'partial'])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'customers_crt')]
#[Gedmo\SoftDeleteable]
class CustomerRelationshipTeam implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['customer_relationship_team', 'customer_relationship_team_detail', 'customer_relationship_team_write'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer', inversedBy: 'crt')]
    #[Assert\NotNull]
    #[Groups(['customer_relationship_team', 'customer_relationship_team_detail', 'customer_relationship_team_write'])]
    #[Transferable(manager: 'manager.customer')]
    #[Legacy\Column(column: 'customer_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId', 'nullValue' => 0])]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['customer_relationship_team', 'customer_relationship_team_detail', 'customer_relationship_team_write'])]
    #[Transferable(manager: 'manager.sales.representative')]
    #[Legacy\Column(column: 'sales_rep_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId', 'nullValue' => 0])]
    private ?People $salesRepresentative = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['customer_relationship_team', 'customer_relationship_team_detail', 'customer_relationship_team_write'])]
    #[Transferable(manager: 'manager.parts.representative')]
    #[Legacy\Column(column: 'parts_rep_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId', 'nullValue' => 0])]
    private ?People $partsRepresentative = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['customer_relationship_team', 'customer_relationship_team_detail', 'customer_relationship_team_write'])]
    #[Transferable(manager: 'manager.service.representative')]
    #[Legacy\Column(column: 'services_rep_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId', 'nullValue' => 0])]
    private ?People $serviceRepresentative = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['customer_relationship_team', 'customer_relationship_team_detail', 'customer_relationship_team_write'])]
    #[ValidLocation(sparePartsHub: true)]
    #[Legacy\Column(column: 'parts_location_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId', 'nullValue' => 0])]
    private ?Location $partsLocation = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['customer_relationship_team', 'customer_relationship_team_detail', 'customer_relationship_team_write'])]
    #[ValidLocation(serviceHub: true)]
    #[Legacy\Column(column: 'services_location_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId', 'nullValue' => 0])]
    private ?Location $serviceLocation = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['customer_relationship_team', 'customer_relationship_team_detail', 'customer_relationship_team_write', 'customer_crt', 'location_public'])]
    #[ValidLocation(sso: true)]
    #[Legacy\Column(column: 'erp_location_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId', 'nullValue' => 0])]
    private ?Location $erpLocation = null;

    #[ORM\Column(name: 'customer_number', type: 'string', length: 6, nullable: true)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 6)]
    #[Legacy\Column(column: 'cuno')]
    private ?string $cuno = null;

    #[Groups(['customer_relationship_team', 'customer_relationship_team_detail', 'customer_relationship_team_write', 'customer_crt'])]
    #[ORM\Column(name: 'customer_business_partner_code', type: 'string', length: 10, nullable: true)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 10)]
    #[Legacy\Column(column: 'cuno')]
    private ?string $customerBusinessPartnerCode = null;

    #[ORM\Column(name: 'deleted_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<ExtranetUserAcl>
     */
    #[ORM\OneToMany(mappedBy: 'crt', targetEntity: 'App\Entity\Sales\ExtranetUserAcl', cascade: ['persist'])]
    private Collection $acls;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['customer_relationship_team_detail'])]
    private ?int $taskId = null;

    public function __construct()
    {
        $this->acls = new ArrayCollection();
    }

    /**
     * check https://github.com/symfony/symfony/issues/35660
     * and https://github.com/symfony/symfony/issues/35574.
     */
    public function __serialize()
    {
        return [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    /**
     * @return $this
     */
    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getSalesRepresentative(): ?People
    {
        return $this->salesRepresentative;
    }

    /**
     * @return $this
     */
    public function setSalesRepresentative(?People $salesRepresentative): self
    {
        $this->salesRepresentative = $salesRepresentative;

        return $this;
    }

    public function getPartsRepresentative(): ?People
    {
        return $this->partsRepresentative;
    }

    /**
     * @return $this
     */
    public function setPartsRepresentative(?People $partsRepresentative): self
    {
        $this->partsRepresentative = $partsRepresentative;

        return $this;
    }

    public function getServiceRepresentative(): ?People
    {
        return $this->serviceRepresentative;
    }

    /**
     * @return $this
     */
    public function setServiceRepresentative(?People $serviceRepresentative): self
    {
        $this->serviceRepresentative = $serviceRepresentative;

        return $this;
    }

    public function getPartsLocation(): ?Location
    {
        return $this->partsLocation;
    }

    /**
     * @return $this
     */
    public function setPartsLocation(?Location $partsLocation): self
    {
        $this->partsLocation = $partsLocation;

        return $this;
    }

    public function getServiceLocation(): ?Location
    {
        return $this->serviceLocation;
    }

    /**
     * @return $this
     */
    public function setServiceLocation(?Location $serviceLocation): self
    {
        $this->serviceLocation = $serviceLocation;

        return $this;
    }

    public function getErpLocation(): ?Location
    {
        return $this->erpLocation;
    }

    /**
     * @return $this
     */
    public function setErpLocation(?Location $erpLocation): self
    {
        $this->erpLocation = $erpLocation;

        return $this;
    }

    public function getCuno(): ?string
    {
        return $this->cuno;
    }

    /**
     * @return $this
     */
    public function setCuno(?string $cuno): self
    {
        $this->cuno = $cuno;

        return $this;
    }

    public function getCustomerBusinessPartnerCode(): ?string
    {
        return $this->customerBusinessPartnerCode;
    }

    /**
     * @return $this
     */
    public function setCustomerBusinessPartnerCode(?string $customerBusinessPartnerCode): self
    {
        $this->customerBusinessPartnerCode = $customerBusinessPartnerCode;

        return $this;
    }

    public function getDeletedAt(): \DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * @return $this
     */
    public function setDeletedAt(\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection<ExtranetUserAcl>
     */
    public function getAcls(): Collection
    {
        return $this->acls;
    }

    public function addAcl(ExtranetUserAcl $acl): self
    {
        $this->acls->add($acl);

        return $this;
    }

    public function removeAcl(ExtranetUserAcl $acl): self
    {
        $this->acls->removeElement($acl);

        return $this;
    }

    public function getTaskId(): ?int
    {
        return $this->taskId;
    }

    public function setTaskId(?int $taskId): self
    {
        $this->taskId = $taskId;

        return $this;
    }
}
