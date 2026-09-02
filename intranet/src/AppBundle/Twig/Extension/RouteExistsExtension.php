<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouteExistsExtension extends AbstractExtension
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('route_exists', [$this, 'routeExists']),
        ];
    }

    public function routeExists(string $name): bool
    {
        return null !== $this->router->getRouteCollection()->get($name);
    }
}
