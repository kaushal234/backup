<?php

declare(strict_types=1);

namespace App\Config;

use App\Client\ApiClient;
use App\Controller\HomeController;
use App\Controller\LoginController;
use App\Controller\LogoutController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Routing
{
    private $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public function map(RouteCollection $routes): void
    {
        $routes->add('home', new Route('/', ['_controller' => HomeController::class]));
        $routes->add('login', new Route('/login', ['_controller' => LoginController::class], [], [], null, [], [Request::METHOD_POST]));

        if ($this->client->isAuthenticated()) {
            $routes->add('logout', new Route('/logout', ['_controller' => LogoutController::class]));
        }

        if (!$this->client->isAuthenticated()) {
            $routes->add('bounce', new Route('/bounce', ['_controller' => LoginController::class]));
        }
    }
}
