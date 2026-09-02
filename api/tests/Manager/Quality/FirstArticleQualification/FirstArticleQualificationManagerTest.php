<?php

declare(strict_types=1);

namespace App\Tests\Manager\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\PlanItem;
use App\Manager\Quality\FirstArticleQualification\FirstArticleQualificationManager;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class FirstArticleQualificationManagerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider firstArticleQualificationProvider
     */
    public function testGetProgressPercentage(FirstArticleQualification $firstArticleQualification, int $expected): void
    {
        $manager = new FirstArticleQualificationManager();

        self::assertSame(
            $expected,
            $manager->getProgressPercentage($firstArticleQualification)
        );
    }

    public function firstArticleQualificationProvider(): iterable
    {
        $faq = $this->prophesize(FirstArticleQualification::class);
        $faq->getPlan()->shouldBeCalledTimes(1)->willReturn(new ArrayCollection());
        yield 'empty plan is 0%' => [$faq->reveal(), 0];

        $faq = $this->prophesize(FirstArticleQualification::class);
        $faq->getPlan()->shouldBeCalledTimes(1)->willReturn(new ArrayCollection([
            (new PlanItem())->setCompletionRate(100),
        ]));
        yield 'single completed line is 100%' => [$faq->reveal(), 100];

        $faq = $this->prophesize(FirstArticleQualification::class);
        $faq->getPlan()->shouldBeCalledTimes(1)->willReturn(new ArrayCollection([
            (new PlanItem())->setCompletionRate(100),
            (new PlanItem())->setCompletionRate(0),
            (new PlanItem())->setCompletionRate(0),
        ]));
        yield 'one of three completed is 33%' => [$faq->reveal(), 33];

        $faq = $this->prophesize(FirstArticleQualification::class);
        $faq->getPlan()->shouldBeCalledTimes(1)->willReturn(new ArrayCollection([
            (new PlanItem())->setCompletionRate(50),
            (new PlanItem())->setCompletionRate(25),
            (new PlanItem())->setCompletionRate(0),
        ]));
        yield 'partial completion averages to 25%' => [$faq->reveal(), 25];

        $faq = $this->prophesize(FirstArticleQualification::class);
        $faq->getPlan()->shouldBeCalledTimes(1)->willReturn(new ArrayCollection([
            (new PlanItem())->setCompletionRate(100),
            (new PlanItem())->setCompletionRate(100),
        ]));
        yield 'all completed is 100%' => [$faq->reveal(), 100];
    }
}
