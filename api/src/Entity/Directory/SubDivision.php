<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
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
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\Feature;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['subdivision', 'division', 'people_public', 'expose_legacy']]),
        new Post(security: "is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"),
        new Post(
            uriTemplate: '/sub_divisions/{id}/logo',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getLogo', 'class' => SubDivisionFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_DIVISION_WRITE', object) or is_granted('MOO_DIR')",
            deserialize: false,
            name: 'upload_subdivision_logo',
        ),
        new Get(),
        new Get(
            uriTemplate: '/sub_divisions/{id}/logo/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: SubDivisionFile::class),
                'id' => new Link(fromClass: SubDivision::class),
            ],
            defaults: ['parentProperty' => 'subDivision', 'class' => SubDivisionFile::class],
            controller: DownloadController::class,
            name: 'download_subdivision_logo',
        ),
        new Put(security: "is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"),
        new Delete(security: "is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"),
        new Delete(
            uriTemplate: '/sub_divisions/{id}/logo/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: SubDivisionFile::class),
                'id' => new Link(fromClass: SubDivision::class),
            ],
            defaults: ['parentProperty' => 'subDivision', 'class' => SubDivisionFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_DIVISION_WRITE', object) or is_granted('MOO_DIR')",
            name: 'delete_subdivision_logo',
        ),
    ],
    normalizationContext: ['groups' => ['subdivision:detail', 'region_light', 'division', 'people_public', 'expose_legacy', 'file']],
    denormalizationContext: ['groups' => ['subdivision:write']],
)]
#[ORM\Table(name: 'directory_sub_division')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id',
    'name' => 'partial',
])]
#[Loggable]
#[Legacy\Synchronize(table: 'tld_sub_divisions')]
class SubDivision implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['subdivision', 'subdivision:detail', 'subdivision:light', 'subdivision:write', 'division:tree'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Legacy\Column(column: 'name')]
    public string $name;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Division', inversedBy: 'subDivisions')]
    #[Groups(['subdivision', 'subdivision:detail', 'subdivision:write', 'people:division', 'map_premise_people'])]
    #[Assert\NotNull]
    #[Legacy\Column(column: 'division_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public Division $division;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['subdivision', 'subdivision:detail'])]
    private int $id;

    /**
     * @var Collection<Region>
     */
    #[ORM\OneToMany(mappedBy: 'subDivision', targetEntity: 'App\Entity\Directory\Region')]
    #[Groups(['subdivision:detail', 'division:tree'])]
    private Collection $regions;

    /**
     * @var Collection<Feature>
     */
    #[ORM\ManyToMany(targetEntity: Feature::class, mappedBy: 'subDivisions')]
    private Collection $features;

    /**
     * @var Collection<SubDivisionFile>
     */
    #[ORM\OneToMany(mappedBy: 'subDivision', targetEntity: 'App\Entity\Directory\SubDivisionFile', cascade: ['persist'], orphanRemoval: true)]
    private Collection $files;

    public function __construct()
    {
        $this->regions = new ArrayCollection();
        $this->features = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function addFeature(Feature $feature): self
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->addSubDivision($this);
        }

        return $this;
    }

    public function removeFeature(Feature $feature): self
    {
        if ($this->features->contains($feature)) {
            $feature->removeSubDivision($this);
            $this->features->removeElement($feature);
        }

        return $this;
    }

    /**
     * @return Collection<Feature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRegions(): Collection
    {
        return $this->regions;
    }

    public function addRegion(Region $region): self
    {
        if (!$this->regions->contains($region)) {
            $region->setSubDivision($this);
            $this->regions->add($region);
        }

        return $this;
    }

    public function removeRegion(Region $region): self
    {
        if ($this->regions->contains($region)) {
            $this->regions->removeElement($region);
        }

        return $this;
    }

    #[Groups(['subdivision:detail'])]
    public function getLogo(): ?SubDivisionFile
    {
        if (0 === $this->files->count()) {
            return null;
        }

        return $this->files->first();
    }

    public function setLogo(?SubDivisionFile $file): self
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

    public function addFile(SubDivisionFile $file): self
    {
        $file->setSubDivision($this);
        $this->files->add($file);

        return $this;
    }

    public function removeFile(SubDivisionFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }
}
