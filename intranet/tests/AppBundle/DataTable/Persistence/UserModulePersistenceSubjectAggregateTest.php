<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Persistence;

use PHPUnit\Framework\TestCase;

class UserModulePersistenceSubjectAggregateTest extends TestCase
{
    public function test(): void
    {
        $subject = new UserModulePersistenceSubjectAggregate('42', 'FOO');

        $this->assertSame('42', $subject->getDataTablePersistenceIdentifier());
        $this->assertSame('FOO', $subject->getModuleName());
    }
}
