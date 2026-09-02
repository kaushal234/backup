<?php

declare(strict_types=1);

namespace App\AI\Exception;

use Symfony\AI\Agent\Toolbox\Exception\ToolExecutionExceptionInterface;

class EntityNotFoundException extends \RuntimeException implements ToolExecutionExceptionInterface
{
    public function __construct(
        private string $entityName,
        private int $id,
    ) {
    }

    public function getToolCallResult(): mixed
    {
        return \sprintf('No %s found with id %d', $this->entityName, $this->id);
    }
}
