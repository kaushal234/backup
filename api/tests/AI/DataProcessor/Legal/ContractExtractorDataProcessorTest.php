<?php

declare(strict_types=1);

namespace App\Tests\AI\DataProcessor\Legal;

use ApiPlatform\Metadata\Operation;
use App\AI\DataProcessor\Legal\ContractExtractorDataProcessor;
use App\AI\Mapper\Legal\ContractAnalysisMapper;
use App\AI\Platform\DocumentPlatform;
use App\AI\Platform\PlatformResult;
use App\AI\Prompt\PromptInterface;
use App\AI\Service\AiJsonResponseParser;
use App\Dto\UploadedFile as UploadedFileDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ContractExtractorDataProcessorTest extends TestCase
{
    private const string PDF_FIXTURE = __DIR__.'/../../../fixtures/file.pdf';

    public function testItOrchestratesAiCallParsingAndMapping(): void
    {
        $processor = new ContractExtractorDataProcessor(
            $this->platformReturning('{"contract_title":"Deal","contract_value":1000}'),
            new AiJsonResponseParser(),
            new ContractAnalysisMapper(),
        );

        $dto = new UploadedFileDto(new UploadedFile(self::PDF_FIXTURE, 'doc.pdf', 'application/pdf', test: true));

        $result = $processor->process($dto, $this->createMock(Operation::class));

        self::assertSame('Deal', $result->shortDescription);
        self::assertSame(1000, $result->value);
        self::assertSame([], $result->parties);
        self::assertSame('File analyzed successfully', $result->message);
    }

    private function platformReturning(string $rawResult): DocumentPlatform
    {
        return new readonly class($rawResult) extends DocumentPlatform {
            public function __construct(private string $rawResult)
            {
            }

            public function ask(PromptInterface $prompt, string $filepath, bool $createLog = false, string $model = 'mistral-small-latest'): PlatformResult
            {
                return new PlatformResult($this->rawResult);
            }
        };
    }
}
