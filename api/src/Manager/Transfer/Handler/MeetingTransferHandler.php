<?php

declare(strict_types=1);

namespace App\Manager\Transfer\Handler;

use App\Doctrine\OwnerReflectionBag;
use App\Entity\MinutesOfMeeting\Meeting;

class MeetingTransferHandler extends AbstractTransferHandler
{
    public function handle(?object $source, object $target, OwnerReflectionBag $relation, array $conditions = []): void
    {
        if (Meeting::class !== ($className = $relation->getReflectionClass()->getName()) || 'createdBy' !== ($propertyName = $relation->getReflectionProperty()->getName())) {
            return;
        }

        $queryBuilder = $this->getQueryBuilder($source, $className, $propertyName);
        $queryBuilder->andWhere('o.status != :closed');
        $queryBuilder->setParameter('closed', Meeting::CLOSED);

        $this->update($queryBuilder, $relation->getReflectionProperty()->getName(), $target);
    }

    public function getName(): string
    {
        return 'meeting.poster';
    }
}
