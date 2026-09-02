<?php

declare(strict_types=1);

namespace AppBundle\Manager\Service;

use AppBundle\Manager\AuditLogManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class TechnicianOnCallAuditManagerTest extends TestCase
{
    use ProphecyTrait;

    public function testFactoryFlagAudit()
    {
        $auditLogManagerProphecy = $this->prophesize(AuditLogManager::class);
        $auditLogManagerProphecy
            ->timeByReference('technician_on_call', 'factoryFlag', 19, '1')
            ->shouldBeCalledOnce()
            ->willReturn(new \DateInterval('PT1H'));

        $technicianOnCallAuditManager = new TechnicianOnCallAuditManager($auditLogManagerProphecy->reveal());
        $interval = $technicianOnCallAuditManager->factoryFlagAudit(['id' => 19]);

        self::assertInstanceOf(\DateInterval::class, $interval);
    }

    public function testUnitOperationalStatusAudit()
    {
        $auditLogManagerProphecy = $this->prophesize(AuditLogManager::class);
        $auditLogManagerProphecy
            ->timeByReference('technician_on_call', 'unitOperationalStatus', 19, 'NMC')
            ->shouldBeCalledOnce()
            ->willReturn(new \DateInterval('PT1H'));

        $technicianOnCallAuditManager = new TechnicianOnCallAuditManager($auditLogManagerProphecy->reveal());
        $interval = $technicianOnCallAuditManager->unitOperationalStatusAudit(['id' => 19]);

        self::assertInstanceOf(\DateInterval::class, $interval);
    }
}
