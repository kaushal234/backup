<?php

declare(strict_types=1);

namespace App\Doctrine\EventListener;

use App\DataCollector\ActivityCollector;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Comment;
use App\Entity\Activity\Log;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsDoctrineListener(Events::postPersist)]
class ActivityListener implements ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function postPersist(PostPersistEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Activity) {
            return;
        }

        if ($entity instanceof Log) {
            $this->serviceLocator->get(ActivityCollector::class)->addLog($entity);
        }

        if ($entity instanceof Comment) {
            $this->serviceLocator->get(ActivityCollector::class)->addComment($entity);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [ActivityCollector::class];
    }
}
