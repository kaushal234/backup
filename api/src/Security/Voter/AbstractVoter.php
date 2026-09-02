<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

abstract class AbstractVoter extends Voter implements ServiceSubscriberInterface
{
    protected ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedServices(): array
    {
        return [
            Security::class,
        ];
    }

    public function getSecurity(): Security
    {
        return $this->serviceLocator->get(Security::class);
    }
}
