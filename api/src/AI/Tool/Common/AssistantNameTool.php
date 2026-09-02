<?php

declare(strict_types=1);

namespace App\AI\Tool\Common;

use App\AI\Tool\ToolInterface;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
class AssistantNameTool implements ToolInterface
{
    private const string NAME = 'assistant_name';
    private const string DESCRIPTION = 'To give the name of the assistant.';

    public function __invoke(): string
    {
        return 'Alvi';
    }
}
