<?php

declare(strict_types=1);

namespace App\Doctrine\EventListener;

use App\Entity\Purchasing\SupplierRanking\Classification;
use App\Repository\Purchasing\ClassificationRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsDoctrineListener(Events::preRemove)]
class ClassificationListener implements ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [ClassificationRepository::class];
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $classification = $args->getObject();
        if (!$classification instanceof Classification) {
            return;
        }

        $this->serviceLocator->get(ClassificationRepository::class)->deleteClassificationFromRankings($classification);
    }
}
