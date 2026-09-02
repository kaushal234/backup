<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Mink\Session;
use FriendsOfBehat\SymfonyExtension\Driver\SymfonyDriver;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Contracts\Service\Attribute\Required;

trait MinkAwareTrait
{
    private Session $session;

    #[Required]
    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function getDriver(): SymfonyDriver
    {
        /** @var SymfonyDriver $driver */
        $driver = $this->session->getDriver();
        if (!$driver instanceof SymfonyDriver) {
            throw new \RuntimeException(\sprintf("The provided driver class '%s' is not supported", $driver::class));
        }

        return $driver;
    }

    public function getClient(): KernelBrowser
    {
        $client = $this->getDriver()->getClient();

        if (!$client instanceof KernelBrowser) {
            throw new \RuntimeException(\sprintf("The provided client class '%s' is not supported", $client::class));
        }

        return $client;
    }

    public function getClientContainer(): ContainerInterface
    {
        $container = $this->getClient()->getContainer();

        return $container->has('test.service_container') ? $container->get('test.service_container') : $container;
    }

    public function getClientSymfonyProfile(): Profile
    {
        if (false === $profile = $this->getClient()->getProfile()) {
            throw new \RuntimeException('The profiler is disabled. Activate it by setting framework.profiler.only_exceptions to false in your config');
        }

        return $profile;
    }
}
