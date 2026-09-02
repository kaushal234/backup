<?php

declare(strict_types=1);

namespace App\Tests\Report\Handler\Quality;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Report\Handler\Quality\FirstArticleQualificationPlanStatusByFactoryHandler;
use PHPUnit\Framework\TestCase;

class FirstArticleQualificationPlanStatusByFactoryHandlerTest extends TestCase
{
    private FirstArticleQualificationPlanStatusByFactoryHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new FirstArticleQualificationPlanStatusByFactoryHandler();
    }

    public function testHandleReturnsNullOnInvalidArgs(): void
    {
        $this->assertNull($this->handler->handle('InvalidClass', 'location.name', 'planApprovalStatus'));
        $this->assertNull($this->handler->handle(FirstArticleQualification::class, 'invalid.x', 'planApprovalStatus'));
        $this->assertNull($this->handler->handle(FirstArticleQualification::class, 'location.name', 'invalid.y'));
    }

    public function testHandleReturnsNullForStatus(): void
    {
        // status by location is owned by FirstArticleQualificationByLocationHandler.
        $this->assertNull($this->handler->handle(FirstArticleQualification::class, 'location.name', 'status'));
    }
}
