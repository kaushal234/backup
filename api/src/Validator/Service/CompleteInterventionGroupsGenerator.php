<?php

declare(strict_types=1);

namespace App\Validator\Service;

use ApiPlatform\Symfony\Validator\ValidationGroupsGeneratorInterface;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use Symfony\Component\Validator\Constraints\GroupSequence;

class CompleteInterventionGroupsGenerator implements ValidationGroupsGeneratorInterface
{
    /**
     * @param Intervention $object
     *
     * @return array|GroupSequence|string[]
     */
    public function __invoke(object $object): array|GroupSequence
    {
        return \in_array($object->getStatus(), Intervention::COMPLETED_STATUSES, true) ? ['Default', 'Completed'] : ['Default'];
    }
}
