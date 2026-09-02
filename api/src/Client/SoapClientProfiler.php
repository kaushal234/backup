<?php

declare(strict_types=1);

namespace App\Client;

use App\Client\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class SoapClientProfiler implements SoapClientInterface
{
    private readonly SoapClientInterface $decorated;
    private readonly EventDispatcherInterface $eventDispatcher;

    public function __construct(
        SoapClientInterface $decorated,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->decorated = $decorated;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __call($name, $arguments)
    {
        $stopWatch = new Stopwatch(true);
        $stopWatch->start('soapRequest');
        $exception = null;

        try {
            $this->decorated->__call($name, $arguments);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $stopWatch->stop('soapRequest');
        $this->eventDispatcher->dispatch(new RequestEvent($this, $name, (string) $stopWatch->getEvent('soapRequest')->getDuration(), $arguments));
        $stopWatch->reset();

        if ($exception instanceof \Throwable) {
            throw $exception;
        }
    }

    public function __getLastRequest(): ?string
    {
        return $this->decorated->__getLastRequest();
    }

    public function __getLastResponse(): ?string
    {
        return $this->decorated->__getLastResponse();
    }

    public function __getLastRequestHeaders(): ?string
    {
        return $this->decorated->__getLastRequestHeaders();
    }

    public function __getLastResponseHeaders(): ?string
    {
        return $this->decorated->__getLastResponseHeaders();
    }

    public function getCalls(): array
    {
        return $this->decorated->getCalls();
    }

    public function disableSoapCalls()
    {
        return $this->decorated->disableSoapCalls();
    }

    public function enableRecord()
    {
        return $this->decorated->enableRecord();
    }
}
