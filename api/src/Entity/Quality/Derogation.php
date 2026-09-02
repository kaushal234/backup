<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\DataProcessor\Quality\Crab\DerogationRemoveProcessor;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\People;
use App\Entity\UpdatableStatusEntityInterface;
use App\Filter\ColumnsFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Blameable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\Quality\Crab\DerogationRepository')]
#[ORM\Table]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
        ),
        new Get(),
        new Get(
            uriTemplate: '/derogations/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: DerogationFile::class),
                'id' => new Link(fromClass: Derogation::class),
            ],
            defaults: ['parentProperty' => 'derogation', 'class' => DerogationFile::class],
            controller: DownloadController::class,
            name: 'download_derogation_file',
        ),
        new Post(
            denormalizationContext: ['groups' => ['derogation:create']],
            security: 'is_granted("FEATURE_DEROGATION_CREATE")',
        ),
        new Post(
            uriTemplate: '/derogations/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => DerogationFile::class],
            controller: UploadController::class,
            security: 'is_granted("FEATURE_DEROGATION_FILE_UPLOAD")',
            deserialize: false,
            name: 'upload_derogation_file',
        ),
        new Put(),
        new Put(
            uriTemplate: '/derogations/{id}/status',
            denormalizationContext: ['groups' => ['derogation:update_status', 'derogation:comment']],
            security: 'is_granted("FEATURE_DEROGATION_STATUS_REOPEN") or is_granted("FEATURE_DEROGATION_STATUS_CLOSE")',
            name: 'update_derogation_status',
        ),
        new Delete(
            uriTemplate: '/derogations/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: DerogationFile::class),
                'id' => new Link(fromClass: Derogation::class),
            ],
            defaults: ['parentProperty' => 'derogation', 'class' => DerogationFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_DEROGATION_FILE_DELETE')",
            name: 'delete_derogation_file',
        ),
        new Delete(
            security: "is_granted('FEATURE_DEROGATION_DELETE')",
            processor: DerogationRemoveProcessor::class,
        ),
    ],
    routePrefix: 'quality',
    normalizationContext: ['groups' => ['derogation', 'derogation_crabs', 'people_public', 'location_public', 'file', 'derogation:comment']],
    denormalizationContext: ['groups' => ['derogation:update']],
)]
#[ApiFilter(SearchFilter::class, properties: ['status', 'assignor', 'assignee', 'shortDescription' => 'partial', 'crabs', 'crabs.equipmentRecord', 'crabs.equipmentRecord.product'])]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
#[ApiFilter(DateFilter::class, properties: ['dueDate' => 'exact'])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalizationGroupsOverride', 'overrideDefaultGroups' => true, 'whitelist' => ['derogation:list', 'crab:equipment_list', 'equipment_list']])]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
class Derogation implements UpdatableStatusEntityInterface
{
    final public const string OPEN = 'OPEN';

    final public const string ACCEPTED = 'ACCEPTED';

    final public const string DENIED = 'DENIED';

    final public const string ARCHIVED = 'ARCHIVED';

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Blameable(on: 'create')]
    #[Groups(['derogation'])]
    public People $assignor;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['derogation:create', 'derogation', 'derogation:update'])]
    public ?People $assignee = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotNull]
    #[Groups(['derogation', 'derogation:create', 'derogation:list', 'derogation:partial_update'])]
    public string $shortDescription;

    #[ORM\Column(type: 'text')]
    #[Assert\NotNull]
    #[Groups(['derogation', 'derogation:create', 'derogation:partial_update'])]
    public string $description;

    #[Assert\NotBlank(groups: ['derogation:update'])]
    #[Groups(['derogation:update', 'derogation:comment'])]
    public ?string $comment = null;

    #[ORM\Column(type: 'date')]
    #[Groups(['derogation', 'derogation:create', 'derogation:due_date'])]
    #[Assert\GreaterThan('yesterday')]
    public \DateTimeInterface $dueDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['derogation'])]
    public ?\DateTimeInterface $closedAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['derogation'])]
    public ?People $closedBy = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['derogation', 'derogation:list'])]
    private int $id;

    /**
     * @var Collection<Crab>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Quality\Crab', mappedBy: 'derogation')]
    #[Groups(['derogation_crabs', 'derogation:create', 'derogation:list'])]
    private Collection $crabs;

    #[ORM\Column(type: 'string')]
    #[Groups(['derogation', 'derogation:update_status'])]
    private string $status = self::OPEN;

    /**
     * @var Collection<DerogationFile>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Quality\DerogationFile', mappedBy: 'derogation', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['derogation', 'derogation:create'])]
    private Collection $files;

    public function __construct()
    {
        $this->crabs = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<Crab>
     */
    public function getCrabs(): Collection
    {
        return $this->crabs;
    }

    public function addCrab(Crab $crab): self
    {
        if (!$this->crabs->contains($crab)) {
            $this->crabs->add($crab);
            $crab->derogation = $this;
        }

        return $this;
    }

    public function removeCrab(Crab $crab): self
    {
        if ($this->crabs->contains($crab)) {
            $this->crabs->removeElement($crab);
        }

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<DerogationFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(DerogationFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setDerogation($this);
        }

        return $this;
    }

    public function removeFile(DerogationFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }
}
