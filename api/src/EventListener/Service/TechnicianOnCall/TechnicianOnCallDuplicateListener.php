<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE, method: 'setPartialContentOnDuplicateWithAnyError')]
readonly class TechnicianOnCallDuplicateListener
{
    public function setPartialContentOnDuplicateWithAnyError(ResponseEvent $event): void
    {
        $request = $event->getRequest();

        if ('technician_on_call_duplicate' !== $request->attributes->get('_api_operation_name')) {
            return;
        }

        $response = $event->getResponse();

        if ($response->getStatusCode() >= 400) {
            return;
        }

        if (true === $request->attributes->get('_toc_duplicate_has_errors')) {
            $response->setStatusCode(Response::HTTP_PARTIAL_CONTENT);
        }
    }
}
