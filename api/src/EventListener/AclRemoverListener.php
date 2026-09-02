<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Event\UserDeletedEvent;
use App\Event\UserDisabledEvent;
use App\Event\UserEvent;
use App\Repository\AclRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class AclRemoverListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserDisabledEvent::class => 'onUserDisable',
            UserDeletedEvent::class => 'onUserDelete',
        ];
    }

    public function onUserDisable(UserEvent $event)
    {
        $user = $event->getUser();

        if (!$user instanceof People) {
            return;
        }

        if (null !== ($vendorUser = $user->getVendorUserLinked())) {
            $vendorUser
                ->setDisabled(true)
                ->setHidden(true)
            ;
        }

        if (null !== ($extranetUser = $user->getExtranetUserLinked())) {
            $extranetUser
                ->setDisabled(true)
                ->setHidden(true)
            ;
        }
    }

    public function onUserDelete(UserEvent $event)
    {
        $user = $event->getUser();

        if (!$user instanceof People) {
            return;
        }

        /** @var AclRepository $aclRepo */
        $aclRepo = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(Acl::class);
        $aclRepo->removeAclByUser($user);
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
        ];
    }
}
