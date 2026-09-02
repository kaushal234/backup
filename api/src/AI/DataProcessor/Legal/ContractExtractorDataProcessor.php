<?php

declare(strict_types=1);

namespace App\AI\DataProcessor\Legal;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\AI\Dto\Legal\ContractAnalysis;
use App\AI\Mapper\Legal\ContractAnalysisMapper;
use App\AI\Platform\DocumentPlatform;
use App\AI\Prompt\Document\ContractExtractorPrompt;
use App\AI\Service\AiJsonResponseParser;
use App\Dto\UploadedFile;

readonly class ContractExtractorDataProcessor implements ProcessorInterface
{
    public function __construct(
        private DocumentPlatform $platform,
        private AiJsonResponseParser $jsonParser,
        private ContractAnalysisMapper $mapper,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ContractAnalysis
    {
        \assert($data instanceof UploadedFile && null !== $data->file);

        $result = $this->platform->ask(new ContractExtractorPrompt('contract/extract'), $data->file->getPathname());
        $parsed = $this->jsonParser->parse($result->result);

        return $this->mapper->map($parsed);
    }
}
