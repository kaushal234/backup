<?php

declare(strict_types=1);

namespace Alice\Loader;

use App\Alice\Loader\CustomLoader;
use App\Event\EntityChangeEvent;
use App\EventListener\AuditLogListener;
use Fidry\AliceDataFixtures\LoaderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CustomLoaderTest extends TestCase
{
    public function test(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $dispatcher = $this->createMock(EventDispatcher::class);
        $auditLogListener = $this->createMock(AuditLogListener::class);
        $customLoader = new CustomLoader($loader, $dispatcher, $auditLogListener);

        $dispatcher->expects($this->once())->method('removeListener')->with(EntityChangeEvent::class, [$auditLogListener, 'onEntityChange']);
        $customLoader->load([]);
    }
}
