<?php

declare(strict_types=1);

namespace App\Entity\Quality\CalibratedTools;

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
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Controller\Quality\OutOfToleranceForm\OutOfToleranceFormsCreateController;
use App\Controller\Quality\OutOfToleranceForm\OutOfToleranceFormsUpdateController;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\People;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * OutOfToleranceForm.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['out_of_tolerance_forms', 'people_public']]),
        new Post(
            uriTemplate: '/out_of_tolerance_forms/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => OutOfToleranceFormFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_TOOL_WRITE')",
            deserialize: false,
            name: 'upload_form_file',
        ),
        new Post(
            uriTemplate: '/tools/{id}/out_of_tolerance_forms',
            controller: OutOfToleranceFormsCreateController::class,
            openapi: new Operation(
                parameters: [
                    new Parameter(
                        name: 'token',
                        in: 'path',
                        description: 'ID',
                        required: true,
                        schema: ['type' => 'string'],
                    ),
                ],
            ),
            security: "is_granted('FEATURE_TOOL_WRITE')",
            read: false,
        ),
        new Put(
            controller: OutOfToleranceFormsUpdateController::class,
            security: "is_granted('FEATURE_TOOL_WRITE')",
        ),
        new Get(),
        new Get(
            uriTemplate: '/out_of_tolerance_forms/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: OutOfToleranceFormFile::class),
                'id' => new Link(fromClass: OutOfToleranceForm::class),
            ],
            defaults: ['parentProperty' => 'outOfToleranceForm', 'class' => OutOfToleranceFormFile::class],
            controller: DownloadController::class,
            name: 'download_form_file'
        ),
        new Delete(
            uriTemplate: '/out_of_tolerance_forms/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: OutOfToleranceFormFile::class),
                'id' => new Link(fromClass: OutOfToleranceForm::class),
            ],
            defaults: ['parentProperty' => 'outOfToleranceForm', 'class' => OutOfToleranceFormFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_TOOL_WRITE')",
            name: 'delete_form_file',
        ),
        new Delete(security: "is_granted('FEATURE_TOOL_WRITE')"),
    ],
    routePrefix: 'quality/calibrated_tools',
    normalizationContext: ['groups' => ['out_of_tolerance_forms_detail', 'people_public', 'file']],
    denormalizationContext: ['groups' => ['out_of_tolerance_forms_write']],
)]
#[ORM\Table(name: 'out_of_tolerance_form')]
#[ApiFilter(OrderFilter::class)]
#[ApiFilter(SearchFilter::class, properties: ['status' => 'exact', 'impactAnalysis' => 'partial', 'correctiveMeasures' => 'partial', 'analysisBy' => 'exact', 'calibrationLog.tool.createdBy' => 'exact', 'calibrationLog.tool.toolType' => 'exact', 'calibrationLog.tool.locationArea' => 'exact', 'calibrationLog.tool.locationArea.factory' => 'exact', 'calibrationLog.tool.serialNumber' => 'partial'])]
#[App\Loggable]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class OutOfToleranceForm
{
    /**
     * @var string
     */
    final public const IN_PROGRESS = 'IN_PROGRESS';
    /**
     * @var string
     */
    final public const CLOSED = 'CLOSED';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['out_of_tolerance_forms', 'out_of_tolerance_forms_detail'])]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['out_of_tolerance_forms', 'out_of_tolerance_forms_detail', 'out_of_tolerance_forms_write', 'tool_detail'])]
    #[ORM\Column(name: 'status', type: 'string')]
    #[Assert\Choice(choices: [self::IN_PROGRESS, self::CLOSED])]
    #[Assert\NotBlank]
    private string $status = self::IN_PROGRESS;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['out_of_tolerance_forms', 'out_of_tolerance_forms_detail', 'out_of_tolerance_forms_write'])]
    #[ORM\Column(name: 'impactAnalysis', type: 'text', nullable: true)]
    #[Assert\NotBlank]
    private ?string $impactAnalysis = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['out_of_tolerance_forms', 'out_of_tolerance_forms_detail', 'out_of_tolerance_forms_write'])]
    #[ORM\Column(name: 'correctiveMeasures', type: 'text', nullable: true)]
    #[Assert\NotBlank]
    private ?string $correctiveMeasures = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['out_of_tolerance_forms', 'out_of_tolerance_forms_detail', 'out_of_tolerance_forms_write', 'tool_detail'])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'analysisBy', referencedColumnName: 'id')]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'App\Entity\Directory\People')]
    private ?People $analysisBy = null;

    #[ORM\OneToOne(mappedBy: 'outOfToleranceForm', targetEntity: 'App\Entity\Quality\CalibratedTools\CalibrationLog')]
    #[Groups(['out_of_tolerance_forms_detail', 'out_of_tolerance_forms_write'])]
    private ?CalibrationLog $calibrationLog = null;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var ArrayCollection<OutOfToleranceFormFile>
     */
    #[ORM\OneToMany(mappedBy: 'outOfToleranceForm', targetEntity: 'App\Entity\Quality\CalibratedTools\OutOfToleranceFormFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['out_of_tolerance_forms_detail'])]
    private Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return OutOfToleranceForm
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set impactAnalysis.
     *
     * @param string $impactAnalysis
     *
     * @return OutOfToleranceForm
     */
    public function setImpactAnalysis($impactAnalysis)
    {
        $this->impactAnalysis = $impactAnalysis;

        return $this;
    }

    /**
     * Get impactAnalysis.
     *
     * @return string
     */
    public function getImpactAnalysis()
    {
        return $this->impactAnalysis;
    }

    /**
     * Set correctiveMeasures.
     *
     * @param string $correctiveMeasures
     *
     * @return OutOfToleranceForm
     */
    public function setCorrectiveMeasures($correctiveMeasures)
    {
        $this->correctiveMeasures = $correctiveMeasures;

        return $this;
    }

    /**
     * Get correctiveMeasures.
     *
     * @return string
     */
    public function getCorrectiveMeasures()
    {
        return $this->correctiveMeasures;
    }

    /**
     * Set analysisBy.
     *
     * @return OutOfToleranceForm
     */
    public function setAnalysisBy(?People $analysisBy = null)
    {
        $this->analysisBy = $analysisBy;

        return $this;
    }

    /**
     * Get analysisBy.
     *
     * @return People
     */
    public function getAnalysisBy()
    {
        return $this->analysisBy;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return $this
     */
    public function addFile(OutOfToleranceFormFile $file)
    {
        $this->files->add($file);
        $file->setOutOfToleranceForm($this);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeFile(OutOfToleranceFormFile $file)
    {
        $this->files->removeElement($file);

        return $this;
    }

    /**
     * @return OutOfToleranceFormFile[]|ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set calibrationLog.
     *
     * @return OutOfToleranceForm
     */
    public function setCalibrationLog(?CalibrationLog $calibrationLog = null)
    {
        $this->calibrationLog = $calibrationLog;

        return $this;
    }

    /**
     * Get calibrationLog.
     *
     * @return CalibrationLog
     */
    public function getCalibrationLog()
    {
        return $this->calibrationLog;
    }
}
