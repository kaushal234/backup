<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SubRequestManager
{
    private readonly HttpKernelInterface $kernel;

    private readonly UrlGeneratorInterface $router;

    public function __construct(HttpKernelInterface $kernel, UrlGeneratorInterface $router)
    {
        $this->kernel = $kernel;
        $this->router = $router;
    }

    public function doSubRequest(string $route, array $routeParameters, string $method, array $content = []): Response
    {
        $requestContent = json_encode($content, \JSON_THROW_ON_ERROR);

        $uri = $this->router->generate($route, $routeParameters);
        $request = Request::create($uri, $method, [], [], [], [], $requestContent);

        $request->headers->set('Accept', 'application/ld+json');
        $request->headers->set('Content-Type', 'application/ld+json');

        return $this->kernel->handle(
            $request,
            HttpKernelInterface::SUB_REQUEST
        );
    }
}
