<?php

declare(strict_types=1);

namespace App\AI\Dto\Legal;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\AI\DataProcessor\Legal\ContractExtractorDataProcessor;
use App\Dto\UploadedFile;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/contracts/extract',
            inputFormats: ['multipart' => ['multipart/form-data']],
            input: UploadedFile::class,
            processor: ContractExtractorDataProcessor::class
        ),
    ]
)]
final class ContractAnalysis
{
    /**
     * @param ContractParty[] $parties
     */
    public function __construct(
        public ?string $shortDescription = null,
        public ?string $description = null,
        public ?string $startDate = null,
        public ?string $expirationDate = null,
        public ?string $jurisdiction = null,
        public ?int $value = null,
        public ?string $currency = null,
        public ?int $renewalPeriod = null,
        public ?string $renewalUnit = null,
        public ?array $parties = null,
        public ?string $message = null,
    ) {
    }
}
