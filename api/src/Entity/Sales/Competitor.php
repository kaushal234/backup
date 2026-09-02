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
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Doctrine\Mapping\Attributes as App;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['competitor', 'expose_legacy', 'catalogue_type_list']],
        ),
        new Delete(security: "is_granted('FEATURE_COMPETITOR_DELETE')"),
        new Delete(
            uriTemplate: '/competitors/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'competitorFiles', fromClass: CompetitorFile::class),
                'id' => new Link(fromClass: Competitor::class),
            ],
            defaults: ['parentProperty' => 'competitor', 'class' => CompetitorFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_COMPETITOR_EDIT')",
            name: 'delete_competitor_file',
        ),
        new Delete(
            uriTemplate: '/competitors/{id}/logo/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: CompetitorLogoFile::class),
                'id' => new Link(fromClass: Competitor::class),
            ],
            defaults: ['parentProperty' => 'competitor', 'class' => CompetitorLogoFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_COMPETITOR_EDIT')",
            name: 'delete_competitor_logo',
        ),
        new Get(),
        new Get(
            uriTemplate: '/competitors/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'competitorFiles', fromClass: CompetitorFile::class),
                'id' => new Link(fromClass: Competitor::class),
            ],
            defaults: ['parentProperty' => 'competitor', 'class' => CompetitorFile::class],
            controller: DownloadController::class,
            name: 'download_competitor_file',
        ),
        new Put(security: "is_granted('FEATURE_COMPETITOR_EDIT')"),
        new Post(security: "is_granted('FEATURE_COMPETITOR_CREATE')"),
        new Post(
            uriTemplate: '/competitors/{id}/logo',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getLogo', 'class' => CompetitorLogoFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_COMPETITOR_EDIT')",
            deserialize: false,
            name: 'upload_competitor_logo',
        ),
        new Post(
            uriTemplate: '/competitors/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getCompetitorFiles', 'class' => CompetitorFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_COMPETITOR_EDIT')",
            deserialize: false,
            name: 'upload_competitor_file',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['competitor_detail', 'expose_legacy', 'people_public', 'catalogue_type_list', 'file']],
    denormalizationContext: ['groups' => ['competitor_write']],
)]
#[ORM\Table(name: 'competitors')]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial', 'shortDescription' => 'partial', 'description' => 'partial', 'url' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'legacyId' => 'exact', 'productTypes' => 'exact'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroupsOverride', 'overrideDefaultGroups' => true, 'whitelist' => ['competitor_list']])]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
#[Legacy\Synchronize(table: 'cor')]
#[Legacy\ExtraColumn(column: 'parent_id', value: 0)]
class Competitor
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['competitor', 'competitor_detail'])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ORM\Column(name: 'name', type: 'string', length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['competitor', 'competitor_public', 'competitor_detail', 'competitor_write', 'competitor_public', 'competitor_list'])]
    #[Legacy\Column(column: 'company_name', transformer: Utf8ToHtmlEntities::class)]
    private string $name;

    #[ORM\Column(name: 'short_description', type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Groups(['competitor', 'competitor_detail', 'competitor_write'])]
    #[Legacy\Column(column: 'short_desc', transformer: Utf8ToHtmlEntities::class)]
    private ?string $shortDescription = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['competitor_detail', 'competitor_write'])]
    #[Legacy\Column(column: 'comments', transformer: Utf8ToHtmlEntities::class)]
    private ?string $description = null;

    #[ORM\Column(name: 'url', type: 'string', length: 100, nullable: true)]
    #[Assert\Url(requireTld: true)]
    #[Assert\Length(max: 100)]
    #[Groups(['competitor', 'competitor_detail', 'competitor_write'])]
    #[Legacy\Column(column: 'url')]
    private ?string $url = null;

    /**
     * @var Collection<ProductType>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\ProductType')]
    #[Groups(['competitor_detail', 'competitor_write', 'catalogue_type_list'])]
    private Collection $productTypes;

    /**
     * @var Collection<CompetitorLogoFile>
     */
    #[ORM\OneToMany(mappedBy: 'competitor', targetEntity: 'App\Entity\Sales\CompetitorLogoFile', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(max: 1)]
    private Collection $files;

    /**
     * @var Collection<CompetitorFile>
     */
    #[ORM\OneToMany(mappedBy: 'competitor', targetEntity: 'App\Entity\Sales\CompetitorFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['competitor_detail'])]
    private Collection $competitorFiles;

    public function __construct()
    {
        $this->productTypes = new ArrayCollection();
        $this->competitorFiles = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

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

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection<ProductType>
     */
    public function getProductTypes(): Collection
    {
        return $this->productTypes;
    }

    public function addProductType(ProductType $productType): self
    {
        $this->productTypes->add($productType);

        return $this;
    }

    public function removeProductType(ProductType $productType): self
    {
        $this->productTypes->removeElement($productType);

        return $this;
    }

    /**
     * @return Collection<CompetitorFile>
     */
    public function getCompetitorFiles(): Collection
    {
        return $this->competitorFiles;
    }

    public function addCompetitorFile(CompetitorFile $competitorFile): self
    {
        $competitorFile->setCompetitor($this);
        $this->competitorFiles->add($competitorFile);

        return $this;
    }

    public function removeCompetitorFile(CompetitorFile $competitorFile): self
    {
        $this->competitorFiles->removeElement($competitorFile);

        return $this;
    }

    #[Groups(['competitor_detail'])]
    public function getLogo(): ?CompetitorLogoFile
    {
        if (0 === $this->files->count()) {
            return null;
        }

        return $this->files->first();
    }

    public function setLogo(?CompetitorLogoFile $file): self
    {
        if (null === $file) {
            $this->files = new ArrayCollection();

            return $this;
        }

        return $this->addFile($file);
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(CompetitorLogoFile $file): self
    {
        $file->setCompetitor($this);
        $this->files->add($file);

        return $this;
    }

    public function removeFile(CompetitorLogoFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }
}
