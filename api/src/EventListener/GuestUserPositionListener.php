<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Directory\Position;
use App\Entity\MIS\GuestUser\GuestUser;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(Events::prePersist)]
class GuestUserPositionListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $guestUser = $args->getObject();

        if (!$guestUser instanceof GuestUser || null !== $guestUser->getPosition()) {
            return;
        }

        $position = $args->getObjectManager()
            ->getRepository(Position::class)
            ->findOneBy(['code' => Position::GUEST]);

        if ($position instanceof Position) {
            $guestUser->setPosition($position);
        }
    }
}
