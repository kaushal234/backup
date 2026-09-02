<?php

declare(strict_types=1);

namespace App\Alice\Loader;

use App\Event\EntityChangeEvent;
use App\EventListener\AuditLogListener;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class CustomLoader implements LoaderInterface
{
    public array $endOrder = [];

    public function __construct(
        private readonly LoaderInterface $decoratedLoader,
        /** @var EventDispatcher $eventDispatcher */
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AuditLogListener $auditLogListener
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], ?PurgeMode $purgeMode = null): array
    {
        $this->eventDispatcher->removeListener(EntityChangeEvent::class, [$this->auditLogListener, 'onEntityChange']);

        return $this->decoratedLoader->load($fixturesFiles, $parameters, $objects, $purgeMode);
    }
}
