<?php

declare(strict_types=1);

namespace App\Client\EventListener;

use App\Client\DataCollector\SoapCollector;
use App\Client\DataCollector\SoapCollectorQuery;
use App\Client\Event\RequestEvent;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class RequestListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator,
        private readonly LoggerInterface $ionRequestLogger
    ) {
    }

    public function onRequest(RequestEvent $event)
    {
        $collector = $this->serviceLocator->get(SoapCollector::class);
        $client = $event->client;

        $query = new SoapCollectorQuery(new VarCloner());
        $query->setRequestBody($event->resource)
            ->setXMLRequest($client->__getLastRequest())
            ->setRequestHeaders($client->__getLastRequestHeaders())
            ->setOperation($event->operation)
            ->setXMLResponse($client->__getLastResponse())
            ->setResponseHeaders($client->__getLastResponseHeaders())
            ->setExecutionTime($event->executionTime)
        ;

        $collector->addQuery($query);
        $this->ionRequestLogger->debug('request: {request}, response: {response}', [
            'request' => $client->__getLastRequest(),
            'response' => $client->__getLastResponse(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [SoapCollector::class];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest'],
        ];
    }
}
