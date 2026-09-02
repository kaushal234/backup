<?php

declare(strict_types=1);

namespace App\Entity\Quality\CalibratedTools;

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
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CalibrationLog.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['calibration_logs']]),
        new Post(security: "is_granted('FEATURE_TOOL_WRITE')"),
        new Put(security: "is_granted('FEATURE_TOOL_WRITE')"),
        new Delete(security: "is_granted('FEATURE_TOOL_WRITE')"),
        new Post(
            uriTemplate: '/calibration_logs/{id}/file',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getCertificate', 'class' => CalibrationLogFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_TOOL_WRITE')",
            deserialize: false,
            name: 'upload_calibration_log_file',
        ),
        new Get(),
        new Get(
            uriTemplate: '/calibration_logs/{id}/file/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: CalibrationLogFile::class),
                'id' => new Link(fromClass: CalibrationLog::class),
            ],
            defaults: ['parentProperty' => 'calibrationLog', 'class' => CalibrationLogFile::class],
            controller: DownloadController::class,
            security: "is_granted('FEATURE_TOOL_WRITE')",
            name: 'download_calibration_log_file',
        ),
    ],
    routePrefix: 'quality/calibrated_tools',
    normalizationContext: ['groups' => ['calibration_logs_detail', 'file']],
    denormalizationContext: ['groups' => ['calibration_logs_write']],
)]
#[ORM\Table(name: 'calibration_log')]
#[ApiFilter(OrderFilter::class)]
#[ApiFilter(DateFilter::class, properties: ['startDate', 'endDate'])]
#[ApiFilter(SearchFilter::class, properties: ['outOfToleranceForm' => 'exact', 'tool'])]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class CalibrationLog
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['calibration_logs', 'calibration_logs_detail'])]
    private int $id;

    /**
     * Many logs have one tool.
     */
    #[Groups(['calibration_logs', 'calibration_logs_detail', 'calibration_logs_write'])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\CalibratedTools\Tool', inversedBy: 'calibrationLogs')]
    #[ORM\JoinColumn(name: 'tool_id', referencedColumnName: 'id')]
    #[Assert\NotBlank]
    private ?Tool $tool = null;

    #[Groups(['calibration_logs', 'calibration_logs_detail', 'calibration_logs_write', 'tool_detail'])]
    #[ApiProperty(iris: ['https://schema.org/Date'])]
    #[ORM\Column(name: 'startDate', type: 'datetime')]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'datetime')]
    private \DateTimeInterface $startDate;

    #[Groups(['calibration_logs', 'calibration_logs_detail', 'calibration_logs_write', 'tool_detail'])]
    #[ApiProperty(iris: ['https://schema.org/Date'])]
    #[ORM\Column(name: 'endDate', type: 'datetime', nullable: true)]
    #[Assert\Type(type: 'datetime')]
    private ?\DateTimeInterface $endDate = null;

    /**
     * Many Calibration logs have one Out of tolerance form.
     */
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['calibration_logs', 'calibration_logs_detail', 'calibration_logs_write', 'tool_detail'])]
    #[ORM\OneToOne(inversedBy: 'calibrationLog', targetEntity: 'App\Entity\Quality\CalibratedTools\OutOfToleranceForm')]
    #[ORM\JoinColumn(name: 'out_of_tolerance_form_id', referencedColumnName: 'id')]
    #[Assert\Type(type: '\App\Entity\Quality\CalibratedTools\OutOfToleranceForm')]
    #[Assert\Valid]
    private ?OutOfToleranceForm $outOfToleranceForm = null;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[Groups(['calibration_logs', 'calibration_logs_detail', 'calibration_logs_write', 'tool_detail'])]
    #[ApiProperty(iris: ['https://schema.org/Date'])]
    #[ORM\Column(name: 'calibration_date', type: 'date', nullable: true)]
    #[Assert\Type('DateTimeInterface')]
    private ?\DateTime $calibrationDate = null;

    /**
     * @var Collection<CalibrationLogFile>
     */
    #[ORM\OneToMany(mappedBy: 'calibrationLog', targetEntity: 'App\Entity\Quality\CalibratedTools\CalibrationLogFile', cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(max: 1)]
    private Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setTool(?Tool $tool = null): self
    {
        $this->tool = $tool;

        return $this;
    }

    public function getTool(): Tool
    {
        return $this->tool;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function setOutOfToleranceForm(?OutOfToleranceForm $outOfToleranceForm = null): self
    {
        $this->outOfToleranceForm = $outOfToleranceForm;

        return $this;
    }

    /**
     * @return OutOfToleranceForm
     */
    public function getOutOfToleranceForm()
    {
        return $this->outOfToleranceForm;
    }

    public function setCalibrationDate(\DateTime $calibrationDate): self
    {
        $this->calibrationDate = $calibrationDate;

        return $this;
    }

    public function getCalibrationDate(): ?\DateTime
    {
        return $this->calibrationDate;
    }

    #[Groups(['calibration_logs', 'calibration_logs_detail', 'calibration_logs_write', 'tool_detail'])]
    public function getCertificate(): ?CalibrationLogFile
    {
        if (0 === $this->files->count()) {
            return null;
        }

        return $this->files->first();
    }

    public function setCertificate(?CalibrationLogFile $file): self
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

    public function addFile(CalibrationLogFile $file): self
    {
        $file->setCalibrationLog($this);
        $this->files->add($file);

        return $this;
    }

    public function removeFile(CalibrationLogFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }
}
