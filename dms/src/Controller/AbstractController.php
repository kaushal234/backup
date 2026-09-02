<?php

declare(strict_types=1);

namespace App\Controller;

use App\Client\ApiClient;
use App\HttpFoundation\MainRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouteCollection;

abstract class AbstractController
{
    /**
     * @var ApiClient
     */
    private $client;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var RouteCollection
     */
    private $router;
    /**
     * @var MainRequest
     */
    private $request;

    public function __construct(ApiClient $client, Session $session, RouteCollection $router, MainRequest $request)
    {
        $this->client = $client;
        $this->session = $session;
        $this->router = $router;
        $this->request = $request;
    }

    public function getClient(): ApiClient
    {
        return $this->client;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getRouter(): RouteCollection
    {
        return $this->router;
    }

    public function getRequest(): MainRequest
    {
        return $this->request;
    }

    public function redirectToReferer()
    {
        return new RedirectResponse($_SERVER['HTTP_REFERER'] ?? $_SERVER['SCRIPT_NAME']);
    }

    public function redirectToHome()
    {
        return new RedirectResponse($_SERVER['SCRIPT_NAME']);
    }
}
