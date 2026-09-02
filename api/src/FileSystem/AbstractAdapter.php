<?php

declare(strict_types=1);

namespace App\FileSystem;

use App\FileSystem\Persistence\PersistedFileFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

abstract class AbstractAdapter implements ServiceSubscriberInterface, AdapterInterface
{
    public function __construct(
        private readonly AdapterContextProviderInterface $contextProvider,
        protected ContainerInterface $locator
    ) {
    }

    public function getContextProvider(): AdapterContextProviderInterface
    {
        return $this->contextProvider;
    }

    public static function getSubscribedServices(): array
    {
        return [
            ValidatorInterface::class,
            PersistedFileFactory::class,
            Security::class,
            PropertyAccessorInterface::class,
            EntityManagerInterface::class,
        ];
    }
}
