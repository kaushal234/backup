<?php

declare(strict_types=1);

namespace App\Doctrine\EventListener;

use App\Entity\Purchasing\SupplierRanking\ExpertiseLevel;
use App\Repository\Purchasing\ExpertiseLevelRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsDoctrineListener(Events::preRemove)]
class ExpertiseLevelListener implements ServiceSubscriberInterface
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
        return [ExpertiseLevelRepository::class];
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $expertiseLevel = $args->getObject();
        if (!$expertiseLevel instanceof ExpertiseLevel) {
            return;
        }

        $this->serviceLocator->get(ExpertiseLevelRepository::class)->deleteExpertiseLevelFromRankings($expertiseLevel);
    }
}
