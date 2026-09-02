<?php

declare(strict_types=1);

namespace App\Postman\Factory;

use App\Postman\Resource\Url;
use Symfony\Component\Routing\Route;

class UrlFactory
{
    public function create(Route $route): Url
    {
        $url = new Url();
        $url->raw = '{{TLD-url}}'.$route->getPath();
        $url->addHost('{{TLD-url}}');
        $url->addPath($route->getPath());

        return $url;
    }
}
