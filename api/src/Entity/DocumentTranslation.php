<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\DataProcessor\DocumentTranslation\DocumentTranslationUploadProcessor;
use App\DataProvider\DocumentTranslation\DocumentTranslationDownloadDataProvider;
use App\DataProvider\DocumentTranslation\DocumentTranslationRefreshDataProvider;
use App\Dto\DeeplTranslator;
use App\Entity\Directory\People;
use App\Repository\DocumentTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DocumentTranslationRepository::class)]
#[UniqueEntity(
    fields: ['documentId', 'documentKey'],
    message: 'This DeepL document (ID+key) already exists.'
)]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/document_translation',
            inputFormats: ['multipart' => ['multipart/form-data']],
            output: DocumentTranslation::class,
            validate: false,
            name: 'document_translation',
            processor: DocumentTranslationUploadProcessor::class,
        ),
        new GetCollection(
            uriTemplate: '/document_translations',
            normalizationContext: ['groups' => ['document_translations']],
            name: 'document_translation_collection',
        ),
        new Get(
            uriTemplate: '/document_translations/{id}/refresh',
            normalizationContext: ['groups' => ['document_translation:refresh']],
            name: 'document_translation_refresh',
            provider: DocumentTranslationRefreshDataProvider::class,
        ),
        new Get(
            uriTemplate: '/document_translations/{id}/download',
            output: false,
            name: 'document_translation_download',
            provider: DocumentTranslationDownloadDataProvider::class,
        ),
    ]
)]
#[ApiFilter(OrderFilter::class, properties: ['createdAt'])]
class DocumentTranslation
{
    public const string STATUS_QUEUED = 'queued';
    public const string STATUS_READY = 'ready';
    public const string STATUS_FAILED = 'failed';

    private const array ALLOWED_MIME_TYPES = [
        // Office OpenXML
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        // PDF
        'application/pdf',
        // HTML
        'text/html',
        // Texte
        'text/plain',
        // XLIFF
        'application/xliff+xml',
        'application/xml',
        'text/xml',
        // SRT (peu standardisé)
        'application/x-subrip',
        'subtitle/srt',
    ];
    #[ORM\Column(name: 'document_id', length: 128)]
    #[Assert\NotBlank(groups: ['post_upload'])]
    public ?string $documentId = null;

    #[ORM\Column(name: 'document_key', length: 256)]
    #[Assert\NotBlank(groups: ['post_upload'])]
    public ?string $documentKey = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['document_translation:refresh', 'document_translations'])]
    public ?int $estimatedSeconds = null;

    #[Assert\Choice(choices: DeeplTranslator::SUPPORTED_LANGUAGE_CODES)]
    #[ORM\Column(length: 12)]
    #[Assert\NotBlank]
    #[Groups(['document_translations'])]
    public string $targetLang = DeeplTranslator::DEFAULT_LANGUAGE_CODE;

    #[Assert\Choice(choices: DeeplTranslator::LEVEL_FORMALITIES)]
    #[ORM\Column(length: 11)]
    #[Assert\NotBlank]
    #[Groups(['document_translations'])]
    public string $formality = DeeplTranslator::DEFAULT_FORMALITY;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['document_translations'])]
    public string $filename;

    #[ORM\Column(length: 128)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::ALLOWED_MIME_TYPES)]
    public string $mimeType;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\LessThanOrEqual(31457280)] // 30 * 1024 * 1024 = 30Mo
    #[Assert\PositiveOrZero]
    #[Groups(['document_translations'])]
    public ?int $size = null;

    #[ORM\Column(length: 32)]
    #[Assert\Choice(choices: [
        self::STATUS_QUEUED,
        self::STATUS_READY,
        self::STATUS_FAILED,
    ])]
    #[Groups(['document_translation:refresh', 'document_translations'])]
    public string $status = self::STATUS_QUEUED;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Gedmo\Blameable(on: 'create')]
    public People $user;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['document_translations'])]
    public ?\DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    public ?\DateTime $updatedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['document_translation:refresh', 'document_translations'])]
    public ?string $errorMessage = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['document_translations'])]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
