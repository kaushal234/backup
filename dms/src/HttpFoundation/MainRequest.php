<?php

declare(strict_types=1);

namespace App\HttpFoundation;

use Symfony\Component\HttpFoundation\Request;

class MainRequest
{
    private $request;
    private $currentRoute;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->currentRoute = $this->transformAsRoute();
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function transformAsRoute(): string
    {
        /** @var array|null $mParameter */
        $mParameter = $this->request->query->all('m');
        $route = '/';

        if ([] === $mParameter) {
            return $route;
        }

        ksort($mParameter);

        foreach ($mParameter as $parameter) {
            $route = \sprintf('%s%s/', $route, $parameter);
        }

        return rtrim($route, '/');
    }

    public function request()
    {
        return $this->request->request;
    }

    public function query()
    {
        return $this->request->query;
    }

    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    public function setCurrentRoute($currentRoute): void
    {
        $this->currentRoute = $currentRoute;
    }
}
