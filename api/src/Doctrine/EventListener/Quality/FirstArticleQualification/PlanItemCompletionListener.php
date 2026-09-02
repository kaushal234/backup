<?php

declare(strict_types=1);

namespace App\Doctrine\EventListener\Quality\FirstArticleQualification;

use App\Entity\Directory\People;
use App\Entity\Quality\FirstArticleQualification\PlanItem;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[AsEntityListener(event: Events::prePersist, entity: PlanItem::class)]
#[AsEntityListener(event: Events::preUpdate, entity: PlanItem::class)]
final class PlanItemCompletionListener
{
    private const array GUARDED_FIELDS = [
        'completionRate',
        'description',
        'comment',
        'requestedPriorDelivery',
        'requestedAtPurchaseOrder',
    ];

    public function __construct(private readonly Security $security)
    {
    }

    public function prePersist(PlanItem $planItem): void
    {
        $this->stampIfCompleted($planItem);
    }

    public function preUpdate(PlanItem $planItem, PreUpdateEventArgs $args): void
    {
        $previousRate = $args->hasChangedField('completionRate')
            ? $args->getOldValue('completionRate')
            : $planItem->getCompletionRate();

        $this->stampIfCompleted($planItem);

        $em = $args->getObjectManager();
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet(
            $em->getClassMetadata(PlanItem::class),
            $planItem
        );

        if (100 !== $previousRate) {
            return;
        }

        foreach (self::GUARDED_FIELDS as $field) {
            if ($args->hasChangedField($field)) {
                throw new UnprocessableEntityHttpException('A completed plan item (100%) can no longer be modified.');
            }
        }
    }

    private function stampIfCompleted(PlanItem $planItem): void
    {
        if (100 !== $planItem->getCompletionRate()) {
            return;
        }

        if (null !== $planItem->getValidatedAt()) {
            return;
        }

        $planItem->setValidatedAt(new \DateTime());

        $user = $this->security->getUser();
        if ($user instanceof People) {
            $planItem->setValidatedBy($user);
        }
    }
}
