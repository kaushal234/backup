<?php

declare(strict_types=1);

namespace App\EventListener\Directory;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Entity\Directory\PositionLevel;
use App\Repository\AclRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class StandardAclListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private ContainerInterface $serviceLocator;

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
            KernelEvents::VIEW => [
                ['onUserPositionUpdate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onUserPositionUpdate(ViewEvent $event): void
    {
        $people = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$people instanceof People || !\in_array($request->getMethod(), [Request::METHOD_PUT, Request::METHOD_POST], true)) {
            return;
        }

        if (null === $people->getPosition()) {
            return;
        }

        if ($request->isMethod(Request::METHOD_PUT)) {
            /** @var People $previousUserData */
            $previousUserData = $request->attributes->get('previous_data');
            if ($previousUserData->getPosition() === $people->getPosition()
                && $previousUserData->getBusinessUnit()->getRegion()->getSubDivision()->division === $people->getBusinessUnit()->getRegion()->getSubDivision()->division) {
                return;
            }
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $existingGroups = array_reduce($people->getAcls()->toArray(), static function ($memo, Acl $acl) {
            $memo[] = $acl->getGroup();

            return $memo;
        }, []);

        foreach ($people->getGroupsForDivision() as $group) {
            if (\in_array($group, $existingGroups, true)) {
                continue;
            }
            /** @var AclRepository $repository */
            $repository = $this->serviceLocator->get(AclRepository::class);
            $repository->updatePeopleAclByGroup($people, $group);
        }

        foreach ($people->getAcls() as $acl) {
            if ('ACL_AUTH_INTRANET' === $acl->getGroup()->getName()
                || 'ACL_AUTH_JAVELO' === $acl->getGroup()->getName()
                || 'ACL_AUTH_AGILE' === $acl->getGroup()->getName()
                || $people->getGroupsForDivision()->contains($acl->getGroup())
            ) {
                continue;
            }

            $expirationMonth = \in_array($people->getPosition()->getLevel()->getLabel(), [PositionLevel::ALVEST_STEERING_COMMITTEE, PositionLevel::MANAGERS, PositionLevel::MANAGERS], true) ? 3 : 1;
            $acl->setExpiredAt((new \DateTime())->modify(\sprintf('+ %d months', $expirationMonth)));
        }

        $entityManager->flush();
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            AclRepository::class,
        ];
    }
}
