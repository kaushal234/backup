<?php

declare(strict_types=1);

namespace App\AI\Prompt\Document;

use App\AI\Prompt\AbstractPrompt;

class SummarizePrompt extends AbstractPrompt
{
    public function instructions(): string
    {
        return <<<'PROMPT'
            Create a concise and clear summary of this document.
            PROMPT;
    }
}
