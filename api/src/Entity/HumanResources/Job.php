<?php

declare(strict_types=1);

namespace App\Entity\HumanResources;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\Archived\JobFile;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\BooleanToChar;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\HumanResources\JobRepository')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['expose_legacy', 'people_public', 'business_unit', 'job']]),
        new Post(security: "is_granted('FEATURE_JOB_WRITE')"),
        new Get(),
        new Delete(security: "is_granted('FEATURE_JOB_WRITE')"),
        new Put(security: "is_granted('FEATURE_JOB_WRITE')"),
    ],
    normalizationContext: ['groups' => ['expose_legacy', 'people_public', 'business_unit', 'job', 'job:detail']],
    denormalizationContext: ['groups' => ['job:write']],
)]
#[ORM\Table(name: 'jobs')]
#[ApiFilter(SearchFilter::class, properties: ['businessUnit'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'businessUnit.name'])]
#[ApiFilter(BooleanFilter::class, properties: ['enabled'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['description' => 'partial', 'title' => 'partial'])]
#[Loggable]
#[Legacy\Synchronize(table: 'hr_jobs')]
class Job
{
    use LegacyIdentifierTrait;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['job', 'job:detail'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['job', 'job:detail'])]
    #[Legacy\Column(column: 'assignor', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Gedmo\Blameable(on: 'create')]
    private People $createdBy;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['job'])]
    #[Legacy\Column(column: 'date', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\BusinessUnit')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['job', 'job:detail', 'job:write'])]
    #[Legacy\Column(column: 'buid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private BusinessUnit $businessUnit;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(max: 100)]
    #[Groups(['job', 'job:write'])]
    #[Legacy\Column(column: 'title', transformer: Utf8ToHtmlEntities::class)]
    private string $title;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 100)]
    #[Groups(['job:detail', 'job:write'])]
    #[Legacy\Column(column: 'experience', transformer: Utf8ToHtmlEntities::class)]
    private ?string $experience = null;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 150)]
    #[Groups(['job:detail', 'job:write'])]
    #[Legacy\Column(column: 'diploma', transformer: Utf8ToHtmlEntities::class)]
    private ?string $diploma = null;

    #[ORM\Column(type: 'text')]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['job:detail', 'job:write'])]
    #[Legacy\Column(column: 'description', transformer: Utf8ToHtmlEntities::class)]
    private string $description;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['job', 'job:write'])]
    #[Legacy\Column(column: 'status', transformer: BooleanToChar::class, options: ['trueValue' => 'OPEN', 'falseValue' => 'CLOSED'])]
    private bool $enabled = true;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['job', 'job:write'])]
    private bool $synchronized = true;

    /**
     * @var Collection<JobFile>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Archived\JobFile', mappedBy: 'job', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['job:detail'])]
    private Collection $jobFiles;

    public function __construct()
    {
        $this->jobFiles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedBy(): People
    {
        return $this->createdBy;
    }

    public function setCreatedBy(People $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getBusinessUnit(): BusinessUnit
    {
        return $this->businessUnit;
    }

    public function setBusinessUnit(BusinessUnit $businessUnit): self
    {
        $this->businessUnit = $businessUnit;

        return $this;
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

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    public function getDiploma(): ?string
    {
        return $this->diploma;
    }

    public function setDiploma(?string $diploma): self
    {
        $this->diploma = $diploma;

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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function setSynchronized(bool $synchronized): self
    {
        $this->synchronized = $synchronized;

        return $this;
    }

    public function getJobFiles(): Collection
    {
        return $this->jobFiles;
    }

    public function addJobFile(JobFile $jobFile): self
    {
        $jobFile->setJob($this);
        $this->jobFiles->add($jobFile);

        return $this;
    }

    public function removeJobFile(JobFile $jobFile): self
    {
        $this->jobFiles->removeElement($jobFile);

        return $this;
    }
}
