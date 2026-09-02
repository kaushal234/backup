<?php

declare(strict_types=1);

namespace App\Entity\Sales\AircraftCompatibility;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\DataProcessor\Sales\AircraftCompatibility\AircraftCompatibilityDataProcessor;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Sales\Product;
use App\Filter\SimpleSearchFilter;
use App\Repository\Sales\AircraftCompatibilityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AircraftCompatibilityRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Delete(security: "is_granted('FEATURE_DELETE_AIRCRAFT_COMPATIBILITY') or is_granted('MOO_AC')"),
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            security: "is_granted('FEATURE_CREATE_AIRCRAFT_COMPATIBILITY') or is_granted('MOO_AC')",
            processor: AircraftCompatibilityDataProcessor::class
        ),
        new Post(
            uriTemplate: '/aircraft_compatibilities/{id}',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            security: "is_granted('FEATURE_EDIT_AIRCRAFT_COMPATIBILITY') or is_granted('MOO_AC')",
            name: 'edit_aircraft_compatibilities',
            processor: AircraftCompatibilityDataProcessor::class
        ),
        new Get(
            uriTemplate: '/aircraft_compatibilities/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: AircraftCompatibilityFile::class),
                'id' => new Link(fromClass: AircraftCompatibility::class),
            ],
            defaults: ['parentProperty' => 'aircraftCompatibility', 'class' => AircraftCompatibilityFile::class],
            controller: DownloadController::class,
            name: 'download_aircraft_compatibility_file',
        ),
        new Delete(
            uriTemplate: '/aircraft_compatibilities/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: AircraftCompatibilityFile::class),
                'id' => new Link(fromClass: AircraftCompatibility::class),
            ],
            defaults: ['parentProperty' => 'aircraftCompatibility', 'class' => AircraftCompatibilityFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_EDIT_AIRCRAFT_COMPATIBILITY') or is_granted('MOO_AC')",
            name: 'delete_aircraft_compatibility_file',
        ),
        new Post(
            uriTemplate: '/aircraft_compatibilities/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => AircraftCompatibilityFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_EDIT_AIRCRAFT_COMPATIBILITY') or is_granted('MOO_AC')",
            deserialize: false,
            name: 'upload_aircraft_compatibility_file',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['aircraft_compatibility', 'catalogue_public', 'aircraft', 'file', 'people_public', 'manufacturer']],
    denormalizationContext: ['groups' => ['aircraft_compatibility:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['aircrafts', 'products', 'products.family.productType', 'products.family', 'aircrafts.manufacturer'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'aircrafts.name', 'products.name'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'exact',
    'products.name' => 'partial',
    'aircrafts.name' => 'partial',
])]
#[App\Loggable]
class AircraftCompatibility
{
    #[ORM\Column(name: 'legacy_id', type: 'integer', nullable: true)]
    public ?int $legacyId = null;

    /**
     * @var Collection<Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class)]
    #[Assert\Count(min: 1)]
    #[Groups(groups: ['aircraft_compatibility', 'aircraft_compatibility:write'])]
    private Collection $products;

    /**
     * @var Collection<Aircraft>
     */
    #[ORM\ManyToMany(targetEntity: Aircraft::class)]
    #[Assert\Count(min: 1)]
    #[Groups(groups: ['aircraft_compatibility', 'aircraft_compatibility:write'])]
    private Collection $aircrafts;

    /**
     * @var Collection<AircraftCompatibilityFile>
     */
    #[ORM\OneToMany(targetEntity: AircraftCompatibilityFile::class, mappedBy: 'aircraftCompatibility', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(groups: ['aircraft_compatibility'])]
    private Collection $files;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(groups: ['aircraft_compatibility'])]
    private int $id;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->aircrafts = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(AircraftCompatibilityFile $file): self
    {
        $file->setAircraftCompatibility($this);
        $this->files->add($file);

        return $this;
    }

    public function removeFile(AircraftCompatibilityFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    /**
     * @return Collection<Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        $this->products->add($product);

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }

    /**
     * @return Collection<Aircraft>
     */
    public function getAircrafts(): Collection
    {
        return $this->aircrafts;
    }

    public function addAircraft(Aircraft $aircraft): self
    {
        $this->aircrafts->add($aircraft);

        return $this;
    }

    public function removeAircraft(Aircraft $aircraft): self
    {
        $this->aircrafts->removeElement($aircraft);

        return $this;
    }
}
