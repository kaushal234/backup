<?php

declare(strict_types=1);

namespace App\Workflow;

use Symfony\Component\Workflow\Registry;

class WorkflowStatusParser
{
    private readonly Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param object $subject
     */
    public function getAvailableStatuses($subject): array
    {
        $availableStatuses = [];

        $workflow = $this->registry->get($subject);
        $transitions = $workflow->getEnabledTransitions($subject);

        foreach ($transitions as $transition) {
            $availableStatuses = array_merge($transition->getTos(), $availableStatuses);
        }

        return array_unique($availableStatuses, \SORT_STRING);
    }
}
