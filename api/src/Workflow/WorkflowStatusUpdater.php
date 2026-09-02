<?php

declare(strict_types=1);

namespace App\Workflow;

use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Registry;

class WorkflowStatusUpdater
{
    private readonly Registry $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param object $subject
     *
     * @throws LogicException
     */
    public function applyStatus($subject, string $status, array $context = [])
    {
        // get the workflow service
        $workflow = $this->registry->get($subject);
        $reasons = [];
        // find the first transition matching
        foreach ($workflow->getDefinition()->getTransitions() as $transition) {
            if (!\in_array($status, $transition->getTos(), true) || !\in_array($subject->getStatus(), $transition->getFroms(), true)) {
                continue;
            }

            if ($workflow->can($subject, $name = $transition->getName())) {
                $workflow->apply($subject, $name, $context);

                return;
            }

            $transitionBlockerList = $workflow->buildTransitionBlockerList($subject, $name);
            if (!$transitionBlockerList->isEmpty()) {
                foreach ($transitionBlockerList as $blocker) {
                    $reasons[] = $blocker->getMessage();
                }
            }
        }

        // if current status is the same, just do nothing
        // doing this before the foreach could have side effects in case of simultaneous multiple states
        if (\array_key_exists($status, $workflow->getMarking($subject)->getPlaces())) {
            return;
        }

        throw new LogicException(\sprintf('Status %s is not allowed.%s', $status, [] !== $reasons ? ' Reasons: '.implode(' ', array_unique($reasons)) : ''));
    }
}
