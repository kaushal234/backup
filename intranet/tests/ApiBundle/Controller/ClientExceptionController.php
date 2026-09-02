<?php

declare(strict_types=1);

namespace Tests\ApiBundle\Controller;

use ApiBundle\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ClientExceptionController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Security::class, EventDispatcherInterface::class, KernelInterface::class]);
    }

    #[Route(path: '/client/{statusCode}', methods: ['GET'])]
    public function clientException($statusCode)
    {
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $security = $this->container->get(Security::class);
        $kernel = $this->container->get(KernelInterface::class);

        $httpClient = new MockHttpClient([
            new MockResponse('Catch me if you can', [
                'http_code' => $statusCode,
            ]),
        ]);

        $client = new Client($security, $eventDispatcher, $kernel, $httpClient, [
            'base_uri' => 'https://example.com',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $client->request('/dummy', null, null, Request::METHOD_GET);
    }
}
