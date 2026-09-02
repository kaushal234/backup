<?php

declare(strict_types=1);

namespace LegacyBundle\Factory\Sequence;

use App\Entity\Directory\People;
use LegacyBundle\Model\Sequence;

trait UserSequenceTrait
{
    protected function constructSequence(string $template, People $people, ?People $assignee = null, int $escalationTrigger = 30): Sequence
    {
        $sequence = new Sequence();
        $sequence
            ->setTemplateName($template)
            ->setModule('USER')
            ->setCloseParams([
                'assignor' => $people->getDisplayName(),
                'assignee' => $people->getDisplayName(),
                'module' => 'USER',
            ])
            ->setParentId($people->getLegacyId())
            ->setLocation($people->getBusinessUnit()->getLocation())
            ->setAssignee($assignee ?? $people)
            ->setAssignor($people)
            ->setEscalationTrigger($escalationTrigger)
        ;

        return $sequence;
    }
}
