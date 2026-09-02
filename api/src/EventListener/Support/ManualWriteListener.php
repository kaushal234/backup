<?php

declare(strict_types=1);

namespace App\EventListener\Support;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Dto\Support\ManualInput;
use App\Entity\Support\Manual;
use App\Repository\Finance\ExchangeRateRepository;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ManualWriteListener
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['preWrite', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
            EntityManagerInterface::class,
            ExchangeRateRepository::class,
        ];
    }

    public function preWrite(ViewEvent $event)
    {
        $manual = $event->getControllerResult();
        if (!$manual instanceof Manual) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        if ((!$request->attributes->has(ManualInput::REQUEST_PARAMETER_NAME)) || (!$request->attributes->get(ManualInput::REQUEST_PARAMETER_NAME)->force)) {
            $event->setControllerResult(new Response(null, Response::HTTP_NO_CONTENT));
        }
    }
}
