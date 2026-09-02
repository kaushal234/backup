<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use App\Entity\Directory\JuridicalLocation;
use App\Entity\Directory\Location;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Order;
use App\Validator\Constraints\SalesOrder;
use App\Validator\Constraints\SalesOrderValidator;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class SalesOrderValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    public function testOnValidOrderWithASSOLinkedToBaan()
    {
        $juridicalLocationProphecy = $this->prophesize(JuridicalLocation::class);
        $juridicalLocationProphecy->getId()->shouldBeCalledTimes(1)->willReturn(1_984);
        $juridicalLocation = $juridicalLocationProphecy->reveal();

        $sso = (new Location())->setErp(42)->setErpInLN(true)->setJuridicalLocation($juridicalLocation);

        $reflectionClass = new \ReflectionClass($sso);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($sso, 12);

        $order = (new Order())
            ->setBaanCustomerNumber('Foo')
            ->setSso($sso)
            ->setJuridicalLocation($juridicalLocation)
        ;

        $this->validator->validate($order, new SalesOrder());

        $this->assertNoViolation();
    }

    public function testOnValidOrderWithASSONotLinkedToBaan()
    {
        $order = (new Order())
            ->setSso((new Location())->setErp(42)->setErpInLN(false))
        ;

        $this->validator->validate($order, new SalesOrder());

        $this->assertNoViolation();
    }

    /** @dataProvider specificCustomerProvider */
    public function testOnValidOrderWithASpecificCustomer(?Customer $endUser = null, ?Customer $buyer = null)
    {
        $order = (new Order())
            ->setSso((new Location())->setErp(42)->setErpInLN(true)->setName('Foo'))
        ;
        if (null !== $endUser) {
            $order->setEndUser($endUser);
            $buyer ??= $endUser;
            $order->setBuyer($buyer);
        }

        $this->validator->validate($order, new SalesOrder());

        $this->assertNoViolation();
    }

    public function specificCustomerProvider()
    {
        yield 'no customers (customer validation rule should be trigger instead)' => [];
        yield 'demo' => [(new Customer())->setName(Customer::CUSTOMER_DEMO)];
        yield 'proto' => [(new Customer())->setName(Customer::CUSTOMER_PROTO)];
        yield 'stock' => [(new Customer())->setName(Customer::CUSTOMER_STOCK)];
        yield 'specific end user' => [(new Customer())->setName(Customer::CUSTOMER_DEMO), (new Customer())->setName('Bar')];
        yield 'specific customer' => [(new Customer())->setName('baz'), (new Customer())->setName(Customer::CUSTOMER_STOCK)];
    }

    public function testThrowsErrorOnInforLnBusinessPartnerCode()
    {
        $order = (new Order())
            ->setSso((new Location())->setErp(42)->setErpInLN(true)->setName('Foo'))
            ->setEndUser((new Customer())->setName('Bar'))
            ->setBuyer((new Customer())->setName('DEMO'))
        ;

        $this->validator->validate($order, new SalesOrder());

        $this->buildViolation('This value should not be null for the SSO {{ name }}.')
            ->setParameter('{{ name }}', 'Foo')
            ->atPath('property.path.inforLnBusinessPartnerCode')
            ->assertRaised();
    }

    /** @dataProvider validJuridicalLocationProvider */
    public function testOnValidOrderWithJuridicalLocation(int $ssoId, int $juridicalLocationId)
    {
        $juridicalLocationProphecy = $this->prophesize(JuridicalLocation::class);
        $juridicalLocationProphecy->getId()->shouldBeCalledTimes(1)->willReturn($juridicalLocationId);
        $juridicalLocation = $juridicalLocationProphecy->reveal();

        $ssoProphecy = $this->prophesize(Location::class);
        $ssoProphecy->getId()->shouldBeCalledTimes(1)->willReturn($ssoId);
        $ssoProphecy->getJuridicalLocation()->shouldBeCalledTimes(1);

        $order = (new Order())
            ->setInforLnBusinessPartnerCode('Foo')
            ->setSso($ssoProphecy->reveal())
            ->setJuridicalLocation($juridicalLocation);

        $this->validator->validate($order, new SalesOrder());

        $this->assertNoViolation();
    }

    public function validJuridicalLocationProvider(): \Generator
    {
        yield [36, 3];
        yield [1, 11];
        yield [1, 5];
        yield [1, 9];
    }

    /** @dataProvider invalidJuridicalLocationProvider */
    public function testThrowsErrorOnBadJuridicalLocation(int $ssoId, int $juridicalLocationId)
    {
        $juridicalLocationProphecy = $this->prophesize(JuridicalLocation::class);
        $juridicalLocationProphecy->getId()->shouldBeCalledTimes(1)->willReturn($juridicalLocationId);
        $juridicalLocation = $juridicalLocationProphecy->reveal();

        $ssoProphecy = $this->prophesize(Location::class);
        $ssoProphecy->getId()->shouldBeCalledTimes(1)->willReturn($ssoId);
        $ssoProphecy->getJuridicalLocation()->shouldBeCalledTimes(1);
        $ssoProphecy->getName()->shouldBeCalledTimes(1)->willReturn('Bar');

        $order = (new Order())
            ->setInforLnBusinessPartnerCode('Foo')
            ->setSso($ssoProphecy->reveal())
            ->setJuridicalLocation($juridicalLocation);

        $this->validator->validate($order, new SalesOrder());

        $this->buildViolation('This value is not a valid juridical location for the SSO {{ name }}.')
            ->setParameter('{{ name }}', 'Bar')
            ->atPath('property.path.juridicalLocation')
            ->assertRaised();
    }

    public function invalidJuridicalLocationProvider(): \Generator
    {
        yield [36, 5];
        yield [1, 13];
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new SalesOrderValidator();
    }
}
