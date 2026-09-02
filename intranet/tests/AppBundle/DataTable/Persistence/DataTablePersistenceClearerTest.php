<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Persistence;

use AppBundle\Manager\SettingsManager;
use Kreyu\Bundle\DataTableBundle\Persistence\PersistenceSubjectInterface;
use Kreyu\Bundle\DataTableBundle\Tests\Fixtures\PersistenceSubjectUser;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

class DataTablePersistenceClearerTest extends TestCase
{
    public function testExceptionSubject(): void
    {
        $clearer = new DataTablePersistenceClearer(
            $this->createMock(CacheInterface::class),
            $this->createMock(SettingsManager::class),
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid persistence subject type.');

        $clearer->clear(new PersistenceSubjectUser(), 'test');
    }

    public function testFullClear(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $settingsManager = $this->createMock(SettingsManager::class);

        $calledArgs = [];
        $cache->method('delete')
            ->willReturnCallback(static function ($key) use (&$calledArgs) {
                $calledArgs[] = $key;

                return true;
            });

        $settingsManager->expects($this->once())->method('remove')->with('foo.datatable.test.personalization');

        $clearer = new DataTablePersistenceClearer($cache, $settingsManager);
        $clearer->clear($this->getUser(), 'test');

        $this->assertCount(3, $calledArgs);
        $this->assertSame([
            'test_pagination_42',
            'test_filtration_42',
            'test_sorting_42',
        ], $calledArgs);
    }

    private function getUser(): PersistenceSubjectInterface
    {
        return new UserModulePersistenceSubjectAggregate('42', 'FOO');
    }
}
