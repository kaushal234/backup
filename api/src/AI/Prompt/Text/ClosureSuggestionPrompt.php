<?php

declare(strict_types=1);

namespace App\AI\Prompt\Text;

use App\AI\Prompt\AbstractPrompt;

final class ClosureSuggestionPrompt extends AbstractPrompt
{
    public function instructions(): string
    {
        return <<<'PROMPT'
            You are an expert field service engineer assistant.
            Based solely on the TOC data and comments provided, suggest closure content in English only.
            Return a JSON object with exactly three keys:
            - "symptoms": what was observed on-site
            - "rootCause": the identified root cause
            - "solution": the corrective action taken
            Do not include any explanation or text outside the JSON object.
            PROMPT;
    }
}
