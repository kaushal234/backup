<?php

declare(strict_types=1);

namespace App\Postman\Factory;

use App\Postman\Resource\Request;
use Symfony\Component\Routing\Route;

class RequestFactory
{
    public function __construct(
        private readonly HeaderFactory $headerFactory,
        private readonly UrlFactory $urlFactory,
    ) {
    }

    public function create(Route $route, string $method): Request
    {
        $request = new Request();

        $request->method = $method;
        $request->addHeader($this->headerFactory->create());
        $request->url = $this->urlFactory->create($route);

        return $request;
    }
}
