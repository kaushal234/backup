<?php

declare(strict_types=1);

namespace App\Tests\Report\Handler\Quality;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Report\Handler\Quality\FirstArticleQualificationByLocationHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class FirstArticleQualificationByLocationHandlerTest extends TestCase
{
    use ProphecyTrait;

    private FirstArticleQualificationByLocationHandler $handler;

    protected function setUp(): void
    {
        $iriConverter = $this->prophesize(IriConverterInterface::class);
        $this->handler = new FirstArticleQualificationByLocationHandler($iriConverter->reveal());
    }

    public function testHandleReturnsNullOnInvalidArgs(): void
    {
        $this->assertNull($this->handler->handle('InvalidClass', 'location.name', 'status'));
        $this->assertNull($this->handler->handle(FirstArticleQualification::class, 'invalid.x', 'status'));
        $this->assertNull($this->handler->handle(FirstArticleQualification::class, 'location.name', 'invalid.y'));
    }

    public function testHandleReturnsNullForPlanApprovalStatus(): void
    {
        // planApprovalStatus by location is owned by FirstArticleQualificationPlanStatusByFactoryHandler.
        $this->assertNull($this->handler->handle(FirstArticleQualification::class, 'location.name', 'planApprovalStatus'));
    }
}
