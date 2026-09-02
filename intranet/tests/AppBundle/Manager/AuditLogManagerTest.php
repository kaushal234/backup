<?php

declare(strict_types=1);

namespace AppBundle\Manager;

use ApiBundle\Client;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AuditLogManagerTest extends TestCase
{
    use ProphecyTrait;

    public function testTimeByReference()
    {
        $clientProphecy = $this->prophesize(Client::class);
        $clientProphecy->get('/audit_logs/time_by_reference', [
            'query' => [
                'auditType' => 'type',
                'property' => 'property',
                'referenceId' => 666,
            ],
        ]
        )->shouldBeCalledOnce()->willReturn(['hydra:member' => [
            ['value' => '', 'time' => 1397],
            ['value' => '1', 'time' => 90000],
        ]]);

        $auditManager = new AuditLogManager($clientProphecy->reveal());
        $interval = $auditManager->timeByReference(
            'type',
            'property',
            666,
            '1'
        );

        self::assertSame(1, $interval->d);
        self::assertSame(1, $interval->h);
    }

    public function testTime()
    {
        $clientProphecy = $this->prophesize(Client::class);
        $clientProphecy->get('/audit_logs/time', [
            'query' => [
                'auditType' => 'type',
                'property' => 'property',
                'filter' => 'myFilter',
            ],
        ]
        )->shouldBeCalledOnce()->willReturn(['hydra:member' => [
            ['value' => '', 'time' => 1397],
            ['value' => '1', 'time' => 90000],
        ]]);

        $auditManager = new AuditLogManager($clientProphecy->reveal());
        $interval = $auditManager->time(
            'type',
            'property',
            ['filter' => 'myFilter'],
            '1'
        );

        self::assertSame(1, $interval->d);
        self::assertSame(1, $interval->h);
    }

    public function testReduce()
    {
        $clientProphecy = $this->prophesize(Client::class);
        $auditManager = new AuditLogManager($clientProphecy->reveal());

        $data = [
            ['value' => 'research', 'time' => 60],
            ['value' => 'ignored', 'time' => 35],
            ['value' => 'research', 'time' => 25],
        ];

        $dateInterval = $auditManager->reduce($data, 'research');

        self::assertInstanceOf(\DateInterval::class, $dateInterval);
        self::assertSame(0, $dateInterval->d);
        self::assertSame(1, $dateInterval->i);
        self::assertSame(25, $dateInterval->s);
    }
}
