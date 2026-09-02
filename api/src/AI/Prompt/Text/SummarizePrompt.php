<?php

declare(strict_types=1);

namespace App\AI\Prompt\Text;

use App\AI\Prompt\AbstractPrompt;

class SummarizePrompt extends AbstractPrompt
{
    public function instructions(): string
    {
        return <<<'PROMPT'
            Summarize the following text in 5 lines with simple, clear language, no unnecessary rephrasing and no hallucination
            PROMPT;
    }
}
