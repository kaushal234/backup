<?php

declare(strict_types=1);

namespace App\EventListener\Specification;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Module\Specification\Specification;
use App\Entity\Module\Specification\UserStory;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class UserStoryListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public function updateStatusSpecification(ViewEvent $event): void
    {
        $userStory = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $request = $event->getRequest();

        if (!$userStory instanceof UserStory || !\in_array($method, [Request::METHOD_PUT, Request::METHOD_POST, Request::METHOD_DELETE], true)) {
            return;
        }

        $previousData = $request->attributes->get('previous_data');

        $specification = $userStory->getSpecification();
        $allUserStoriesValidated = true;
        foreach ($specification->getUserStories() as $story) {
            if (UserStory::VALIDATED !== $story->getStatus()) {
                $allUserStoriesValidated = false;
                break;
            }
        }

        $workflowUpdater = $this->serviceLocator->get(WorkflowStatusUpdater::class);

        if (Request::METHOD_PUT === $request->getMethod() && $allUserStoriesValidated) {
            $workflowUpdater->applyStatus($specification, Specification::PRODUCTION);
        } elseif (Request::METHOD_POST === $request->getMethod()) {
            $workflowUpdater->applyStatus($specification, Specification::DEVELOPMENT);
        } elseif (Request::METHOD_DELETE === $request->getMethod() && 1 === \count($specification->getUserStories())) {
            $workflowUpdater->applyStatus($specification, Specification::DEVELOPMENT);
        } elseif (Request::METHOD_PUT === $request->getMethod() && UserStory::VALIDATED === $previousData->getStatus()) {
            $workflowUpdater->applyStatus($userStory, UserStory::HAS_BEEN_EDITED);
            $workflowUpdater->applyStatus($specification, Specification::DEVELOPMENT);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['updateStatusSpecification', EventPriorities::PRE_WRITE],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            WorkflowStatusUpdater::class,
        ];
    }
}
