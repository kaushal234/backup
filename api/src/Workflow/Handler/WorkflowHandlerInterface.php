<?php

declare(strict_types=1);

namespace App\Workflow\Handler;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.workflow.handler')]
interface WorkflowHandlerInterface
{
    public function support(object $current, ?object $previous): bool;

    public function handle(object $current, ?object $previous): bool;
}
