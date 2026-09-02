<?php

declare(strict_types=1);

namespace App\Doctrine\EventListener;

use App\Entity\Common\Airport;
use App\Entity\Common\BusStation;
use App\Entity\Common\FerryPort;
use App\Entity\Common\Heliport;
use App\Entity\Common\MetropolitanArea;
use App\Entity\Common\OffLinePoint;
use App\Entity\Common\RailwayStation;
use App\Entity\IATACode;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(Events::prePersist)]
class IATATypeListener
{
    public function prePersist(PrePersistEventArgs $args)
    {
        $entity = $args->getObject();
        switch (true) {
            case !$entity instanceof IATACode:
                return;
            case $entity instanceof Airport:
                $entity->setType('Airport');

                return;
            case $entity instanceof RailwayStation:
                $entity->setType('Railway Station');

                return;
            case $entity instanceof BusStation:
                $entity->setType('Bus Station');

                return;
            case $entity instanceof OffLinePoint:
                $entity->setType('Off-Line Point');

                return;
            case $entity instanceof MetropolitanArea:
                $entity->setType('Metropolitan Area');

                return;
            case $entity instanceof FerryPort:
                $entity->setType('Ferry Port');

                return;
            case $entity instanceof Heliport:
                $entity->setType('Heliport');

                return;
        }
    }
}
