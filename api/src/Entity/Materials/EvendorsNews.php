<?php

declare(strict_types=1);

namespace App\Entity\Materials;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
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
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_EVENDORS_NEWS_WRITE')"),
        new Post(
            uriTemplate: '/evendors_news/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => EvendorsNewsFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_EVENDORS_NEWS_WRITE')",
            deserialize: false,
            name: 'upload_evendors_news_file',
        ),
        new Put(security: "is_granted('FEATURE_EVENDORS_NEWS_WRITE')"),
        new Get(),
        new Get(
            uriTemplate: '/evendors_news/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: EvendorsNewsFile::class),
                'id' => new Link(fromClass: EvendorsNews::class),
            ],
            defaults: ['parentProperty' => 'evendorsNews', 'class' => EvendorsNewsFile::class],
            controller: DownloadController::class,
            name: 'download_evendors_news_file',
        ),
        new Delete(security: "is_granted('FEATURE_EVENDORS_NEWS_WRITE')"),
        new Delete(
            uriTemplate: '/evendors_news/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: EvendorsNewsFile::class),
                'id' => new Link(fromClass: EvendorsNews::class),
            ],
            defaults: ['parentProperty' => 'evendorsNews', 'class' => EvendorsNewsFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_EVENDORS_NEWS_WRITE')",
            name: 'delete_evendors_news_file',
        ),
    ],
    normalizationContext: ['groups' => ['evendors_news', 'people_public', 'file', 'location_public']],
    denormalizationContext: ['groups' => ['evendors_news_write']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
)]
#[ORM\Table(name: 'evendors_news')]
#[ApiFilter(SearchFilter::class, properties: ['factories.erp'])]
#[ApiFilter(OrderFilter::class, properties: ['publishedAt' => 'DESC'])]
#[ApiFilter(DateFilter::class, properties: ['publishedAt' => DateFilter::INCLUDE_NULL_BEFORE_AND_AFTER, 'unpublishedAt' => DateFilter::INCLUDE_NULL_BEFORE_AND_AFTER])]
class EvendorsNews
{
    #[ORM\Column(name: 'content', type: 'text')]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['evendors_news', 'evendors_news_write'])]
    public string $content;

    #[ORM\Column(type: 'datetime')]
    #[Assert\Type('DateTimeInterface')]
    #[ApiProperty(iris: ['https://schema.org/Date'])]
    #[Groups(['evendors_news', 'evendors_news_write'])]
    public \DateTimeInterface $publishedAt;

    #[ORM\Column(type: 'datetime')]
    #[Assert\Type('DateTimeInterface')]
    #[Assert\GreaterThan(propertyPath: 'publishedAt')]
    #[ApiProperty(iris: ['https://schema.org/Date'])]
    #[Groups(['evendors_news', 'evendors_news_write'])]
    public \DateTimeInterface $unpublishedAt;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['evendors_news'])]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    #[Groups(['evendors_news', 'evendors_news_write'])]
    #[Gedmo\Blameable(on: 'create')]
    public People $createdBy;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['evendors_news'])]
    #[Gedmo\Timestampable(on: 'update')]
    public ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['evendors_news'])]
    private int $id;
    /**
     * @var Collection<Location>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\Location')]
    #[Assert\All(constraints: new ValidLocation(factory: true))]
    #[Assert\Count(min: 1)]
    #[Groups(['evendors_news', 'evendors_news_write'])]
    private Collection $factories;

    /**
     * @var Collection<EvendorsNewsFile>
     */
    #[Groups(['evendors_news', 'evendors_news_write'])]
    #[ORM\OneToMany(mappedBy: 'evendorsNews', targetEntity: 'App\Entity\Materials\EvendorsNewsFile', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(max: 5)]
    private Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->factories = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFactories(): Collection
    {
        return $this->factories;
    }

    /**
     * @return $this
     */
    public function addFactory(Location $factory): self
    {
        if (!$this->factories->contains($factory)) {
            $this->factories->add($factory);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeFactory(Location $factory): self
    {
        if ($this->factories->contains($factory)) {
            $this->factories->removeElement($factory);
        }

        return $this;
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    /**
     * @return $this
     */
    public function addFile(EvendorsNewsFile $file): self
    {
        $file->setEvendorsNews($this);
        $this->files->add($file);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeFile(EvendorsNewsFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }
}
