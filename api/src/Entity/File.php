<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\File\ResizeController;
use App\Dto\FileInput;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * File.
 */
#[ApiResource(
    operations: [
        new Get(),
        new Put(security: "is_granted('DESCRIPTION_FILE_VOTER', object) or is_granted('FEATURE_NON_CONFORMITY_FILE_CHANGE_VISIBILITY') or is_granted('FEATURE_CRAB_FILE_CHANGE_VISIBILITY') or is_granted('FEATURE_CSR_FILE_CHANGE_VISIBILITY') or is_granted('FEATURE_VWC_FILE_CHANGE_VISIBILITY')"),
        new Post(
            uriTemplate: '/files/{id}/resize',
            controller: ResizeController::class,
            input: FileInput::class,
            validate: true,
            name: 'resize_file'
        ),
    ],
    denormalizationContext: [],
    output: false,
)]
#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap([
    'calibration_out_of_tolerance' => 'App\Entity\Quality\CalibratedTools\OutOfToleranceFormFile',
    'spq_attached_file' => 'App\Entity\SPQ\AttachedFile',
    'spq_quotation_file' => 'App\Entity\SPQ\QuotationFile',
    'customer_file' => 'App\Entity\Sales\CustomerFile',
    'support_accident_file' => 'App\Entity\Support\AccidentFile',
    'support_maintenance_file' => 'App\Entity\Support\MaintenanceFile',
    'competitor_file' => 'App\Entity\Sales\CompetitorFile',
    'competitor_pricing_file' => 'App\Entity\Sales\CompetitorPricingFile',
    'forecast_closure_file' => 'App\Entity\Sales\ForecastClosureFile',
    'sales_forecast_file' => 'App\Entity\Sales\SalesForecastFile',
    'faq_file' => 'App\Entity\Quality\FirstArticleQualification\FirstArticleQualificationFile',
    'demo_file' => 'App\Entity\Sales\DemoFile',
    'sales_order_file' => 'App\Entity\Sales\OrderFile',
    'transportation_note_file' => 'App\Entity\Parts\TransportationNoteFile',
    'market_intelligence_file' => 'App\Entity\Sales\MarketIntelligence\MarketIntelligenceFile',
    'meeting_file' => 'App\Entity\MinutesOfMeeting\MeetingFile',
    'training_file' => 'App\Entity\Archived\TrainingFile',
    'product_certificate_file' => 'App\Entity\Sales\ProductCertificateFile',
    'news_file' => 'App\Entity\News\NewsFile',
    'calibration_log_file' => 'App\Entity\Quality\CalibratedTools\CalibrationLogFile',
    'people_file' => 'App\Entity\Directory\PeopleFile',
    'customer_logo_file' => 'App\Entity\Sales\CustomerLogoFile',
    'competitor_logo_file' => 'App\Entity\Sales\CompetitorLogoFile',
    'job_file' => 'App\Entity\Archived\JobFile',
    'shipping_quotation_request_file' => 'App\Entity\Archived\ShippingQuotationRequestFile',
    'tracking_file_file' => 'App\Entity\Parts\TrackingFile',
    'spr_proof_of_delivery_file' => 'App\Entity\Parts\ProofOfDeliveryFile',
    'spare_parts_request_file' => 'App\Entity\Parts\SparePartsRequestFile',
    'comment_file' => 'App\Entity\Activity\CommentFile',
    'manual_document_files' => 'App\Entity\Support\ManualDocumentFile',
    'non_conformity_file' => 'App\Entity\Quality\NonConformityFile',
    'non_conformity_main_file' => 'App\Entity\Quality\NonConformityMainFile',
    'supplier_corrective_action_request_file' => 'App\Entity\Quality\SupplierCorrectiveActionRequestFile',
    'vendor_warranty_claim_file' => 'App\Entity\Purchasing\VendorWarrantyClaimFile',
    'supplier_corrective_action_request_main_file' => 'App\Entity\Quality\SupplierCorrectiveActionRequestMainFile',
    'evendors_news_file' => 'App\Entity\Materials\EvendorsNewsFile',
    'vendor_warranty_claim_main_file' => 'App\Entity\Purchasing\VendorWarrantyClaimMainFile',
    'subdivision_file' => 'App\Entity\Directory\SubDivisionFile',
    'crab_file' => 'App\Entity\Quality\CrabFile',
    'crab_main_file' => 'App\Entity\Quality\CrabMainFile',
    'derogation_file' => 'App\Entity\Quality\DerogationFile',
    'equipment_shipping_record_file' => 'App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordFile',
    'supplier_rankings_files' => 'App\Entity\Purchasing\SupplierRanking\SupplierRankingFile',
    'trouble_ticket_file' => 'App\Entity\MIS\TroubleTicket\TroubleTicketFile',
    'customer_service_record_file' => 'App\Entity\Service\CustomerServiceRecord\CustomerServiceRecordFile',
    'security_review_file' => 'App\Entity\Module\ThirdPartyApp\SecurityReviewFile',
    'account_review_file' => 'App\Entity\Module\ThirdPartyApp\AccountReviewFile',
    'user_story_file' => 'App\Entity\Module\Specification\UserStoryFile',
    'task_file' => 'App\Entity\Task\TaskFile',
    'project_file' => 'App\Entity\MIS\Project\ProjectFile',
    'pictogram_file' => 'App\Entity\Engineering\Pictogram\PictogramFile',
    'contract_file' => 'App\Entity\Legal\ContractFile',
    'aircraft_compatibility_file' => 'App\Entity\Sales\AircraftCompatibility\AircraftCompatibilityFile',
    'ai_file' => 'App\Entity\AI\AIFile',
    'toc_main_file' => 'App\Entity\Service\TechnicianOnCallMainFile',
    'toc_file' => 'App\Entity\Service\TechnicianOnCallFile',
])]
#[ORM\Table(name: 'files')]
abstract class File implements \Stringable
{
    public const LIVE = 'LIVE';
    public const ARCHIVED = 'ARCHIVED';

    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[Groups(['file', 'file_public_write'])]
    #[ORM\Column(name: 'public', type: 'boolean', nullable: true, options: ['default' => 0])]
    protected bool $public = false;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    #[Groups(['file', 'file_description_write', 'file:description'])]
    protected ?string $description = null;
    #[Groups(['file', 'file:light'])]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Groups(['file', 'file:light'])]
    #[ORM\Column(name: 'file_path', type: 'text')]
    private string $filePath;

    #[Groups(['file'])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    private ?User $poster = null;

    #[Groups(['file', 'file:light'])]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[Groups(['file'])]
    #[ORM\Column(name: 'sha', type: 'string', length: 128)]
    private string $sha;

    #[Groups(['file', 'file:light'])]
    #[ORM\Column(name: 'mime_type', type: 'string', length: 255)]
    private string $mimeType;

    #[Groups(['file'])]
    #[ORM\Column(name: 'extension', type: 'string', length: 8)]
    private string $extension;

    #[Groups(['file'])]
    #[ORM\Column(name: 'size', type: 'integer')]
    private int $size;

    #[ORM\Column(type: 'string', length: 8, nullable: true)]
    #[Assert\Choice(choices: [self::LIVE, self::ARCHIVED])]
    private ?string $status = null;

    public function __toString()
    {
        return $this->getFilePath();
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return $this
     */
    public function setFilePath(string $filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getPoster(): ?User
    {
        return $this->poster;
    }

    public function setPoster(User $poster): self
    {
        $this->poster = $poster;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSha(): string
    {
        return $this->sha;
    }

    public function setSha(string $sha): self
    {
        $this->sha = $sha;

        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return $this
     */
    public function setStatus(?string $status)
    {
        $this->status = $status;

        return $this;
    }
}
