<?php

declare(strict_types=1);

namespace App\EventListener\Specification;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Module\Module;
use App\Entity\Module\Specification\Specification;
use App\Entity\Module\ThirdPartyApp\Type\Connected;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Entity\Module\ThirdPartyApp\Type\Light;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class CreateSpecificationListener implements EventSubscriberInterface
{
    // if the specification is creating in child(light extended) class of module response 422 with message
    // "Specification should be created only on module"

    public function createSpecification(ViewEvent $event): void
    {
        $specification = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$specification instanceof Specification || !\in_array($method, [Request::METHOD_POST], true)) {
            return;
        }

        $module = $specification->getModule();

        if ($module instanceof Connected || $module instanceof Extended || $module instanceof Light || 'DISABLED' === $module->status) {
            throw new UnprocessableEntityHttpException('Specification should be created only on valid module');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['createSpecification', EventPriorities::PRE_WRITE],
        ];
    }
}
