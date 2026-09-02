<?php

declare(strict_types=1);

namespace App\Workflow\Handler;

class ChainWorkflowHandler
{
    public function __construct(
        private readonly iterable $workflowHandlers,
    ) {
    }

    /**
     * Return true if change has been made otherwise return false.
     */
    public function handle(object $current, ?object $previous)
    {
        foreach ($this->workflowHandlers as $handler) {
            if (!$handler->support($current, $previous)) {
                continue;
            }

            $handler->handle($current, $previous);
        }
    }
}
