<?php

declare(strict_types=1);

namespace App\EventListener\Directory;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\People;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PeopleContractTypeFilterSecurityListener implements EventSubscriberInterface
{
    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['restrictFilter', EventPriorities::PRE_READ]],
        ];
    }

    public function restrictFilter(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->isMethod(Request::METHOD_GET)) {
            return;
        }

        if (People::class !== $request->attributes->get('_api_resource_class')) {
            return;
        }

        if ($request->query->has('contractType')
            && !$this->authorizationChecker->isGranted('CONTRACT_TYPE_ADMIN_VOTER')
            && !$this->authorizationChecker->isGranted('FEATURE_EMPLOYEE_STAFFING_REPORT')
        ) {
            throw new BadRequestHttpException('You are not granted the required permissions to search people by contract type.');
        }
    }
}
