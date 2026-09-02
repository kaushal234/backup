<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Sales\SalesForecast;

use App\Entity\Sales\SalesForecast;
use App\EventListener\Sales\SalesForecast\SalesForecastWriteListener;
use App\Request\Activity\CommentRequestManager;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SalesForecastWriteListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function provideNotificationTestCases()
    {
        yield 'Regular comment' => ["Comment t'as pu faire ça ?", "Comment t'as pu faire ça ?", false];
        yield 'Restricted comment' => ['Chuuuuuut', 'Restricted comment: Chuuuuuut', true];
    }

    /**
     * @dataProvider provideNotificationTestCases
     */
    public function testCommentIsInsertedAfterSalesForecastEdition(string $originalComment, string $insertedComment, bool $restricted)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $commentManagerProphecy = $this->prophesize(CommentRequestManager::class);

        $salesForecast = (new SalesForecast())
            ->setComment($originalComment)
            ->setNotificationRestricted($restricted)
            ->setStatus(SalesForecast::BUDGET)
        ;

        $serviceLocatorProphecy->get(CommentRequestManager::class)->shouldBeCalledTimes(1)->willReturn($commentManagerProphecy->reveal());
        $commentManagerProphecy->insertComment($salesForecast, $insertedComment)->shouldBeCalledTimes(1);

        $listener = new SalesForecastWriteListener($serviceLocatorProphecy->reveal());

        $request = new Request();
        $request->setMethod('PUT');
        $listener->afterSalesForecastEdition(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $salesForecast));
    }
}
