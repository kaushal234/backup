<?php

declare(strict_types=1);

namespace AppBundle\Monolog\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ModuleProcessor implements EventSubscriberInterface, ProcessorInterface
{
    private $module;

    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['module'] = $this->module;

        return $record;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if ($event->isMainRequest()) {
            $this->module = $event->getRequest()->attributes->get('alvest_module');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 1],
        ];
    }
}
