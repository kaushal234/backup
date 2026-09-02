<?php

declare(strict_types=1);

namespace App\Entity\Engineering\Pictogram;

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
use App\Controller\Engineering\Pictogram\PictureUploadController;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Doctrine\Mapping\Attributes as App;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'pictograms')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_PICTOGRAM_CREATE')"),
        new Post(
            uriTemplate: '/pictograms/{id}/picture',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getPicture', 'class' => PictogramFile::class],
            controller: PictureUploadController::class,
            security: "is_granted('FEATURE_PICTOGRAM_CREATE')",
            read: true,
            deserialize: false,
            name: 'upload_pictogram_picture',
        ),
        new Post(
            uriTemplate: '/pictograms/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => PictogramFile::class],
            controller: UploadController::class,
            security: 'is_granted("FEATURE_PICTOGRAM_CREATE")',
            deserialize: false,
            name: 'upload_pictogram_file'
        ),
        new Get(),
        new Get(
            uriTemplate: '/pictograms/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: PictogramFile::class),
                'id' => new Link(fromClass: Pictogram::class),
            ],
            defaults: ['parentProperty' => 'pictogram', 'class' => PictogramFile::class],
            controller: DownloadController::class,
            name: 'download_pictogram_file',
        ),
        new Delete(security: "is_granted('FEATURE_PICTOGRAM_DELETE')"),
        new Delete(
            uriTemplate: '/pictograms/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: PictogramFile::class),
                'id' => new Link(fromClass: Pictogram::class),
            ],
            defaults: ['parentProperty' => 'pictogram', 'class' => PictogramFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_PICTOGRAM_DELETE')",
            name: 'delete_pictogram_file',
        ),
        new Put(security: "is_granted('FEATURE_PICTOGRAM_UPDATE')"),
    ],
    routePrefix: 'engineering',
    normalizationContext: ['groups' => ['pictogram:read', 'pictogram_category:read', 'file:light']],
    denormalizationContext: ['groups' => ['pictogram:write']],
)]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'exact',
    'description' => 'partial',
    'category.name' => 'partial',
])]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'description' => 'partial',
    'category',
])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'description', 'category.name'])]
#[App\Loggable]
class Pictogram
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    #[Groups(['pictogram:read', 'pictogram:write'])]
    public string $description;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['pictogram:read', 'pictogram:write'])]
    public Category $category;
    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['pictogram:read'])]
    private int $id;

    /**
     * @var Collection<PictogramFile>
     */
    #[ORM\OneToMany(targetEntity: PictogramFile::class, mappedBy: 'pictogram', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(max: 10)]
    #[Groups(['pictogram:write'])]
    private Collection $files;

    public function getId(): int
    {
        return $this->id;
    }

    #[Groups(['pictogram:read'])]
    public function getFiles(): Collection
    {
        // Exclude the main picture.
        return new ArrayCollection(
            array_values(array_filter(
                $this->files->toArray(),
                static fn (PictogramFile $file) => false === $file->main
            ))
        );
    }

    public function setFiles(Collection $files): void
    {
        $this->files = $files;
    }

    public function addFile(PictogramFile $file): self
    {
        $file->pictogram = $this;
        $this->files->add($file);

        return $this;
    }

    public function removeFile(PictogramFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    #[Groups(['pictogram:read'])]
    public function getPicture(): ?PictogramFile
    {
        foreach ($this->files->toArray() as $file) {
            if ($file->main) {
                return $file;
            }
        }

        return null;
    }

    public function setPicture(?PictogramFile $file): self
    {
        if (null === $file) {
            $this->files = new ArrayCollection();

            return $this;
        }

        return $this->addFile($file);
    }
}
