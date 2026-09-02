<?php

declare(strict_types=1);

namespace App\AI\Tool\Common;

use App\AI\Resource\AcronymResource;
use Mcp\Capability\Attribute\McpTool;

#[McpTool(
    name: 'resolve_internal_acronym',
    description: <<<'TEXT'
        Use this tool whenever the user message contains an internal company acronym.

        An acronym is usually:
        - 2 to 5 uppercase letters
        - such as TIR, TTS, TOC, CRAB, ERP

        Use this tool even if:
        - the acronym is inside a sentence
        - the user does not explicitly ask for a definition

        Examples:
        - "What is a TTS?"
        - "Who validates the TIR?"
        - "Explain TOC workflow"
        - "Which SSO is selling towbarless tractors?"

        Always prefer this tool before answering from general knowledge.
        TEXT
)]
readonly class AcronymTool
{
    public function __construct(
        private AcronymResource $resource
    ) {
    }

    public function __invoke(string $acronym): array
    {
        $data = $this->resource->getAcronym(mb_strtoupper($acronym));

        return [
            [
                'type' => 'text',
                'text' => $data['text'],
            ],
            [
                'type' => 'resource_link',
                'uri' => $data['uri'],   // ← optional
            ],
        ];
    }
}
