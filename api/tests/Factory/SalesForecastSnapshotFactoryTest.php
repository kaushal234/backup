<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Sales\SalesForecast;
use App\Entity\Sales\SalesForecastSnapshot;
use App\Factory\SalesForecastSnapshotFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class SalesForecastSnapshotFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateSnapshot()
    {
        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);

        $salesForecast = new SalesForecast();

        $argumentInstanceOfSnapshot = static fn ($snapshot) => $snapshot instanceof SalesForecastSnapshot;

        $propertyAccessorProphecy->getValue($salesForecast, 'id')->shouldNotBeCalled();
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'id', Argument::any())->shouldNotBeCalled();
        $propertyAccessorProphecy->getValue($salesForecast, 'snapshotCreatedAt')->shouldNotBeCalled();
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'snapshotCreatedAt', Argument::any())->shouldNotBeCalled();
        $propertyAccessorProphecy->getValue($salesForecast, 'originalSalesForecast')->shouldNotBeCalled();
        $propertyAccessorProphecy->setValue(Argument::type(SalesForecastSnapshot::class), 'originalSalesForecast', Argument::any())->shouldNotBeCalled();
        $propertyAccessorProphecy->getValue($salesForecast, 'masterSalesForecast')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'masterSalesForecast', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'createdAt')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'createdAt', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'updatedAt')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'updatedAt', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'lastCommentedAt')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'lastCommentedAt', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'status')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'status', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'lastComment')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'lastComment', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'sso')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'sso', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'factory')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'factory', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'asm')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'asm', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'poster')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'poster', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'equoteId')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'equoteId', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'buyer')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'buyer', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'endUser')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'endUser', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'thirdParty')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'thirdParty', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'country')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'country', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'airport')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'airport', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'product')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'product', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'quantity')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'quantity', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'estimatedSaleDate')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'estimatedSaleDate', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'customerSuccessPercentage')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'customerSuccessPercentage', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'successPercentage')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'successPercentage', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'tier')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'tier', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'delinquent')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'delinquent', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'price')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'price', Argument::any())->shouldBeCalledTimes(1);
        $propertyAccessorProphecy->getValue($salesForecast, 'margin')->shouldBeCalledTimes(1)->willReturn(Argument::any());
        $propertyAccessorProphecy->setValue(Argument::that($argumentInstanceOfSnapshot), 'margin', Argument::any())->shouldBeCalledTimes(1);

        $factory = new SalesForecastSnapshotFactory($propertyAccessorProphecy->reveal());
        $factory->createSnapshot($salesForecast);
    }
}
