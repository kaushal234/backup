<?php

declare(strict_types=1);

namespace App\AI\Exception;

use Symfony\AI\Agent\Toolbox\Exception\ToolExecutionExceptionInterface;

class AccessDeniedException extends \RuntimeException implements ToolExecutionExceptionInterface
{
    public function getToolCallResult(): mixed
    {
        return 'Access denied';
    }
}
