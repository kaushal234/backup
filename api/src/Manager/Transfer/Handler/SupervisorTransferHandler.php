<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\Directory\People;

class SupervisorTransferHandler extends AbstractTransferHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle($source, $target, OwnerReflectionBag $relation, array $conditions = []): void
    {
        if (!$this->supports($relation->getReflectionClass()->getName(), $relation->getReflectionProperty())) {
            return;
        }

        $teamMembers = $this->em->getRepository(People::class)->getSubordinates($source, 1);
        foreach ($teamMembers as $teamMember) {
            $supervisor = $target->getSupervisor() === $source ? $source->getSupervisor() : $target;
            $teamMember->setSupervisor($supervisor);
            $this->em->persist($teamMember);
        }

        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'handler.transfer.supervisor';
    }

    /**
     * {@inheritdoc}
     */
    private function supports(string $className, \ReflectionProperty $property): bool
    {
        return People::class === $className && 'supervisor' === $property->getName();
    }
}
