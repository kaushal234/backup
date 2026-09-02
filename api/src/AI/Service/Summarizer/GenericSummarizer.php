<?php

declare(strict_types=1);

namespace App\AI\Service\Summarizer;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\AI\Dto\SummaryOutput;
use App\AI\Platform\TextPlatform;
use App\AI\Prompt\Text\SummarizePrompt;
use App\AI\Service\Extractor\GenericExtractor;
use App\Entity\DMS;
use App\Entity\Sales\SalesForecast;
use App\Entity\Service\TechnicianOnCall;

#[FeatureDoc(path: 'ai-summarizer.md')]
final readonly class GenericSummarizer
{
    /**
     * @var array<class-string, string>
     */
    private const PROMPT_PATHS = [
        DMS::class => 'summarize/dms',
        SalesForecast::class => 'summarize/sfr',
        TechnicianOnCall::class => 'summarize/toc',
    ];

    public function __construct(
        private GenericExtractor $extractor,
        private TextPlatform $platform,
    ) {
    }

    public function summarize(string $class, array $uriVariables, bool $createLog): SummaryOutput
    {
        $content = $this->extractor->extract($class, $uriVariables);

        $platformResult = $this->platform->ask(
            new SummarizePrompt($this->getPromptPath($class), $uriVariables),
            $content,
            $createLog,
        );

        $result = new SummaryOutput();
        $result->logIri = $platformResult->logIri;
        $result->summary = $platformResult->result;

        return $result;
    }

    private function getPromptPath(string $class): string
    {
        return self::PROMPT_PATHS[$class] ?? 'summarize/generic';
    }
}
