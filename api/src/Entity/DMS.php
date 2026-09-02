<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\AI\DataProvider\Summarize\SummarizeDataProvider;
use App\AI\Dto\SummaryOutput;
use App\Entity\Directory\People;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    shortName: 'dms',
    operations: [
        new GetCollection(),
        new Get(
            formats: ['jsonld', 'json', 'dms' => 'application/vnd.alvest.dms'],
            security: "is_granted('DMS_PEOPLE_VIEW_VOTER', object) or is_granted('EVENDORS_DMS_VIEW_VOTER', object) or is_granted('EXTRANET_DMS_VIEW_VOTER', object)"
        ),
        new Get(
            uriTemplate: '/dms/by_legacy_id/{legacyId}',
            uriVariables: [
                'legacyId' => new Link(identifiers: ['legacyId']),
            ],
            security: "is_granted('DMS_PEOPLE_VIEW_VOTER', object) or is_granted('ACCESS_VENDOR_USER') or is_granted('EXTRANET_DMS_VIEW_VOTER', object)",
            name: 'dms_by_legacy_id',
        ),
        new Get(
            uriTemplate: '/ai/summarize/dms/{legacyId}',
            uriVariables: [
                'legacyId' => new Link(fromProperty: 'legacyId', fromClass: DMS::class),
            ],
            routePrefix: '',
            requirements: ['legacyId' => '[1-9]\d*'],
            openapi: new Operation(
                summary: 'Request a summary using AI',
                parameters: [
                    new Parameter(
                        name: 'legacyId',
                        in: 'path',
                        required: true,
                        schema: ['type' => 'string', 'minimum' => 1],
                    ),
                ],
            ),
            normalizationContext: ['groups' => ['summary']],
            output: SummaryOutput::class,
            name: 'summarize_dms',
            provider: SummarizeDataProvider::class
        ),
    ],
    normalizationContext: ['groups' => ['document', 'expose_legacy', 'people_public']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER') or is_granted('ACCESS_EXTRANET_USER')"
)]
#[ORM\Table(name: 'dms')]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial', 'subject' => 'partial', 'description' => 'partial', 'portal', 'language', 'owner', 'legacyId', 'status' => 'exact'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['legacyId' => 'exact', 'title' => 'partial', 'subject' => 'partial', 'description' => 'partial', 'status' => 'exact'])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['document_list', 'expose_legacy']])]
#[ApiFilter(OrderFilter::class, properties: ['title'])]
class DMS
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['document'])]
    private int $id;

    #[ORM\Column(name: 'title', type: 'string')]
    #[Groups(['document', 'document_list'])]
    private string $title;

    #[ORM\Column(name: 'subject', type: 'string')]
    #[Groups(['document'])]
    private string $subject;

    #[ORM\Column(name: 'description', type: 'text')]
    #[Groups(['document'])]
    private string $description;

    #[ORM\Column(name: 'language', type: 'string', length: 32)]
    #[Groups(['document'])]
    private string $language;

    #[ORM\Column(name: 'portal', type: 'string', length: 10, nullable: true)]
    #[Groups(['document'])]
    private ?string $portal = null;

    #[ORM\Column(type: 'string', length: 60, nullable: true)]
    #[Groups(['document'])]
    private ?string $type = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['document'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['document', 'catalogue_type', 'catalogue_type_detail', 'catalogue_family_dms'])]
    private ?People $owner = null;

    #[ORM\Column(type: 'string', length: 60, nullable: true)]
    #[Groups(['document'])]
    private ?string $status = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['document'])]
    private bool $confidential = false;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['document'])]
    private ?string $revision = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $filepath = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $mimetype = null;

    /** @var Collection<DMSRestriction> */
    #[ORM\OneToMany(targetEntity: DMSRestriction::class, mappedBy: 'dms')]
    private Collection $restrictions;

    public function __construct()
    {
        $this->restrictions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getPortal(): ?string
    {
        return $this->portal;
    }

    public function setPortal(string $portal): self
    {
        $this->portal = $portal;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOwner(): People
    {
        return $this->owner;
    }

    public function setOwner(People $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isConfidential(): bool
    {
        return $this->confidential;
    }

    public function setConfidential(bool $confidential): self
    {
        $this->confidential = $confidential;

        return $this;
    }

    /**
     * @return Collection<DMSRestriction>
     */
    public function getRestrictions(): Collection
    {
        return $this->restrictions;
    }

    public function addRestriction(DMSRestriction $restriction): self
    {
        if (!$this->restrictions->contains($restriction)) {
            $this->restrictions->add($restriction);
        }

        return $this;
    }

    public function removeRestriction(DMSRestriction $restriction): self
    {
        if ($this->restrictions->contains($restriction)) {
            $this->restrictions->removeElement($restriction);
        }

        return $this;
    }

    public function getRevision(): string
    {
        return $this->revision;
    }

    public function setRevision(string $revision): self
    {
        $this->revision = $revision;

        return $this;
    }

    public function getFilepath(): ?string
    {
        return $this->filepath;
    }

    public function setFilepath(?string $filepath): self
    {
        $this->filepath = $filepath;

        return $this;
    }

    public function getMimetype(): ?string
    {
        return $this->mimetype;
    }

    public function setMimetype(?string $mimetype): self
    {
        $this->mimetype = $mimetype;

        return $this;
    }
}
