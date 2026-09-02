<?php

declare(strict_types=1);

namespace App\Entity\Sales;

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
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\Directory\Location;
use App\Entity\EmissionRating;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_PRODUCT_CERTIFICATE_ADMIN') or is_granted('MOO_CAT')"),
        new Post(
            uriTemplate: '/product_certificates/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => ProductCertificateFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_PRODUCT_CERTIFICATE_ADMIN') or is_granted('MOO_CAT')",
            deserialize: false,
            name: 'upload_product_certificate_file',
        ),
        new Put(security: "is_granted('FEATURE_PRODUCT_CERTIFICATE_ADMIN') or is_granted('MOO_CAT')"),
        new Delete(security: "is_granted('FEATURE_PRODUCT_CERTIFICATE_ADMIN') or is_granted('MOO_CAT')"),
        new Delete(
            uriTemplate: '/product_certificates/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: ProductCertificateFile::class),
                'id' => new Link(fromClass: ProductCertificate::class),
            ],
            defaults: ['parentProperty' => 'certificate', 'class' => ProductCertificateFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_PRODUCT_CERTIFICATE_ADMIN') or is_granted('MOO_CAT')",
            name: 'delete_product_certificate_file',
        ),
        new Get(),
        new Get(
            uriTemplate: '/product_certificates/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: ProductCertificateFile::class),
                'id' => new Link(fromClass: ProductCertificate::class),
            ],
            defaults: ['parentProperty' => 'certificate', 'class' => ProductCertificateFile::class],
            controller: DownloadController::class,
            name: 'download_product_certificate_file',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['product_certificate', 'catalogue_public', 'location_public', 'emission_rating', 'file', 'people_public']],
    denormalizationContext: ['groups' => ['product_certificate:write']],
)]
#[ORM\Table]
#[ApiFilter(OrderFilter::class, properties: ['expiredAt'])]
#[ApiFilter(SearchFilter::class, properties: ['product', 'product.family.productType', 'emissionRating', 'factory'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['announcementCertificateNumber' => 'partial'])]
#[ApiFilter(DateFilter::class, properties: ['expiredAt', 'expectedAt'])]
#[Loggable]
class ProductCertificate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['product_certificate'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Product')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['product_certificate', 'product_certificate:write'])]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\EmissionRating')]
    #[Groups(['product_certificate', 'product_certificate:write'])]
    private ?EmissionRating $emissionRating = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['product_certificate', 'product_certificate:write'])]
    private ?int $engineeringActivityProcess = null;

    #[ApiProperty(iris: ['https://schema.org/url'])]
    #[ORM\Column(name: 'url', type: 'string', length: 255, nullable: true)]
    #[Assert\Url(requireTld: true)]
    #[Groups(['product_certificate', 'product_certificate:write'])]
    private ?string $url = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['product_certificate', 'product_certificate:write'])]
    private ?string $testReportNumber = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['product_certificate', 'product_certificate:write'])]
    private ?\DateTimeInterface $expectedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['product_certificate', 'product_certificate:write'])]
    private ?\DateTimeInterface $expiredAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['product_certificate', 'product_certificate:write'])]
    #[ValidLocation(factory: true)]
    private Location $factory;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['product_certificate', 'product_certificate:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['product_certificate', 'product_certificate:write'])]
    private ?string $announcementCertificateNumber = null;

    /**
     * @var Collection<ProductCertificateFile>
     */
    #[ORM\OneToMany(mappedBy: 'certificate', targetEntity: 'App\Entity\Sales\ProductCertificateFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['product_certificate'])]
    private Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getEmissionRating(): ?EmissionRating
    {
        return $this->emissionRating;
    }

    public function setEmissionRating(?EmissionRating $emissionRating): self
    {
        $this->emissionRating = $emissionRating;

        return $this;
    }

    public function getEngineeringActivityProcess(): ?int
    {
        return $this->engineeringActivityProcess;
    }

    public function setEngineeringActivityProcess(?int $engineeringActivityProcess): self
    {
        $this->engineeringActivityProcess = $engineeringActivityProcess;

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

    public function getTestReportNumber(): ?string
    {
        return $this->testReportNumber;
    }

    public function setTestReportNumber(?string $testReportNumber): self
    {
        $this->testReportNumber = $testReportNumber;

        return $this;
    }

    public function getExpectedAt(): ?\DateTimeInterface
    {
        return $this->expectedAt;
    }

    public function setExpectedAt(?\DateTime $expectedAt): self
    {
        $this->expectedAt = $expectedAt;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeInterface
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(?\DateTime $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function setFactory($factory): self
    {
        $this->factory = $factory;

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

    public function getAnnouncementCertificateNumber(): ?string
    {
        return $this->announcementCertificateNumber;
    }

    public function setAnnouncementCertificateNumber(?string $announcementCertificateNumber): self
    {
        $this->announcementCertificateNumber = $announcementCertificateNumber;

        return $this;
    }

    /**
     * @return Collection<ProductCertificateFile>
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(ProductCertificateFile $certificateFile): self
    {
        if (!$this->files->contains($certificateFile)) {
            $certificateFile->setCertificate($this);
            $this->files->add($certificateFile);
        }

        return $this;
    }

    public function removeFile(ProductCertificateFile $certificateFile): self
    {
        if ($this->files->contains($certificateFile)) {
            $this->files->removeElement($certificateFile);
        }

        return $this;
    }
}
