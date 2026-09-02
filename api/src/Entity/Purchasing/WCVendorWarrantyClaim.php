<?php

declare(strict_types=1);

namespace App\Entity\Purchasing;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
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
use App\Controller\Purchasing\VendorWarrantyClaimController;
use App\Entity\Activity\Comment;
use App\Serializer\Normalizer\ActivityNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Controller\DownloadController as LegacyDownloadController;
use LegacyBundle\Entity\LegacyFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    shortName: 'WcVendorWarrantyClaim',
    operations: [
        new GetCollection(
            paginationItemsPerPage: 2000,
            normalizationContext: ['groups' => VendorWarrantyClaim::COLLECTION_NORMALIZATION_GROUPS],
        ),
        new Post(security: "is_granted('FEATURE_WC_VENDOR_WARRANTY_CLAIM_CREATE')"),
        new Put(security: "is_granted('VENDOR_WARRANTY_CLAIM_VOTER', object)"),
        new Put(
            uriTemplate: '/wc_vendor_warranty_claims/{id}/status',
            controller: VendorWarrantyClaimController::class,
            denormalizationContext: ['groups' => ['vendor_warranty_claim:status']],
            security: "is_granted('VENDOR_WARRANTY_CLAIM_STATUS_VOTER', object)",
            name: 'update_wc_vendor_warranty_claim_status',
        ),
        new Post(
            uriTemplate: '/wc_vendor_warranty_claims/{id}/main_file',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getMainFile', 'class' => VendorWarrantyClaimMainFile::class],
            controller: UploadController::class,
            normalizationContext: [],
            security: "is_granted('VENDOR_WARRANTY_CLAIM_VOTER', object)",
            deserialize: false,
            name: 'upload_main_wc_vendor_warranty_claim_file',
        ),
        new Delete(
            uriTemplate: '/wc_vendor_warranty_claims/{id}/main_file/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'mainFiles', fromClass: VendorWarrantyClaimMainFile::class),
                'id' => new Link(fromClass: WCVendorWarrantyClaim::class),
            ],
            defaults: ['parentProperty' => 'vendorWarrantyClaim', 'class' => VendorWarrantyClaimMainFile::class],
            controller: DeleteController::class,
            security: "is_granted('VENDOR_WARRANTY_CLAIM_VOTER', object)",
            name: 'delete_main_wc_vendor_warranty_claim_file',
        ),
        new Get(),
        new Get(
            uriTemplate: '/wc_vendor_warranty_claims/{id}/main_file/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'mainFiles', fromClass: VendorWarrantyClaimMainFile::class),
                'id' => new Link(fromClass: WCVendorWarrantyClaim::class),
            ],
            defaults: ['parentProperty' => 'vendorWarrantyClaim', 'class' => VendorWarrantyClaimMainFile::class],
            controller: DownloadController::class,
            name: 'download_main_wc_vendor_warranty_claim_file',
        ),
        new Get(
            uriTemplate: '/wc_vendor_warranty_claims/{id}/wc_files',
            defaults: ['method' => 'getWarrantyClaimFiles'],
            controller: LegacyDownloadController::class,
            normalizationContext: [],
            name: 'download_wc_vendor_warranty_claim_file',
        ),
        new Get(
            uriTemplate: '/wc_vendor_warranty_claims/{id}/toc_files',
            defaults: ['method' => 'getTocFiles'],
            controller: LegacyDownloadController::class,
            normalizationContext: [],
            name: 'download_toc_wc_vendor_warranty_claim_file',
        ),
    ],
    routePrefix: 'purchasing',
    normalizationContext: ['groups' => VendorWarrantyClaim::ITEM_NORMALIZATION_GROUPS, ActivityNormalizer::NORMALIZE_ACTIVITY_ATTRIBUTE => 'both'],
    denormalizationContext: ['groups' => ['vendor_warranty_claim:create', 'part:admin']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
    extraProperties: [
        Comment::VENDOR_USER_COMMENTABLE => true,
    ],
)]
#[ORM\Table(name: 'vendor_warranty_claim_wc')]
#[ApiFilter(SearchFilter::class, properties: ['warrantyClaimId' => 'exact'])]
class WCVendorWarrantyClaim extends VendorWarrantyClaim
{
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['vendor_warranty_claim', 'vendor_warranty_claim:create'])]
    public int $warrantyClaimId;

    #[Groups(['vendor_warranty_claim:detail'])]
    private array $warrantyClaimFiles = [];

    /**
     * @var Collection<VendorWarrantyClaimMainFile>
     */
    #[ORM\OneToMany(mappedBy: 'vendorWarrantyClaim', targetEntity: 'App\Entity\Purchasing\VendorWarrantyClaimMainFile', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(max: 1)]
    private Collection $mainFiles;

    #[Groups(['vendor_warranty_claim:detail'])]
    private array $tocFiles = [];

    public function __construct()
    {
        parent::__construct();
        $this->mainFiles = new ArrayCollection();
    }

    /**
     * @return array|LegacyFile[]
     */
    public function getWarrantyClaimFiles()
    {
        return $this->warrantyClaimFiles;
    }

    public function addWarrantyClaimFile(LegacyFile $file): self
    {
        $this->warrantyClaimFiles[] = $file;

        return $this;
    }

    public function removeWarrantyClaimFile(LegacyFile $file): self
    {
        return $this;
    }

    /**
     * @return Collection<VendorWarrantyClaimMainFile>
     */
    public function getMainFiles()
    {
        return $this->mainFiles;
    }

    public function addMainFile(VendorWarrantyClaimMainFile $mainFile): self
    {
        if (!$this->mainFiles->contains($mainFile)) {
            $this->mainFiles->add($mainFile);
            $mainFile->setVendorWarrantyClaim($this);
        }

        return $this;
    }

    public function removeMainFile(VendorWarrantyClaimMainFile $mainFile): self
    {
        if ($this->mainFiles->contains($mainFile)) {
            $this->mainFiles->removeElement($mainFile);
        }

        return $this;
    }

    #[Groups(['vendor_warranty_claim:detail'])]
    public function getMainFile(): ?VendorWarrantyClaimMainFile
    {
        if (0 === $this->mainFiles->count()) {
            return null;
        }

        return $this->mainFiles->first();
    }

    public function setMainFile(?VendorWarrantyClaimMainFile $mainFile): self
    {
        if (null === $mainFile) {
            $this->mainFiles = new ArrayCollection();

            return $this;
        }

        return $this->addMainFile($mainFile);
    }

    /**
     * @return array|LegacyFile[]
     */
    public function getTocFiles(): array
    {
        return $this->tocFiles;
    }

    public function addTocFile(LegacyFile $file): self
    {
        $this->tocFiles[] = $file;

        return $this;
    }

    public function removeTocFiles(LegacyFile $file): self
    {
        return $this;
    }
}
