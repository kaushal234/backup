<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Sales\Customer;

use App\Entity\Sales\Customer;
use App\EventListener\Sales\Customer\CustomerDeletionListener;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CustomerDeletionListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testCustomerDeletion()
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);

        $customer = (new Customer())
            ->setName('Customortimer')
        ;

        $listener = new CustomerDeletionListener($serviceLocatorProphecy->reveal());

        $request = new Request();
        $request->setMethod(Request::METHOD_DELETE);

        $serviceLocatorProphecy->get(EntityManagerInterface::class)->shouldBeCalledTimes(1)->willReturn($entityManagerProphecy->reveal());
        $entityManagerProphecy->persist($customer)->shouldBeCalledTimes(1);
        $entityManagerProphecy->flush()->shouldBeCalledTimes(1);

        $listener->onPreDelete(new ViewEvent(static::$kernel, $request, HttpKernelInterface::MAIN_REQUEST, $customer));

        self::assertSame('Customortimer_DELETED', $customer->getName());
    }
}
