<?php

declare(strict_types=1);

namespace App\Tests\Mailer\Sales;

use App\Entity\Directory\People;
use App\Entity\Sales\Order;
use App\Mailer\Sales\OrdersPoolMailer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class OrdersPoolMailerTest extends TestCase
{
    use ProphecyTrait;

    public function testAddingOrderWithoutContactDoesNothing()
    {
        $mailerProphecy = $this->prophesize(MailerInterface::class);

        $mailer = new OrdersPoolMailer($mailerProphecy->reveal());

        self::assertSame($mailer, $mailer->addOrder(new Order(), [], '42'));
    }

    public function testAddingOrderCorrectlyCreateAnEmail()
    {
        $mailerProphecy = $this->prophesize(MailerInterface::class);
        $mailer = new OrdersPoolMailer($mailerProphecy->reveal());

        $order = new Order();

        self::assertSame($mailer, $mailer->addOrder($order, [(new People())->setEmail('foo@lier'), (new People())->setEmail('am@zing.yp')], '42'));

        self::assertSame(1, $mailer->countEmails());
        self::assertInstanceOf(TemplatedEmail::class, $email = $mailer->getEmail('42'));
        $recipients = array_map(static fn (Address $address) => $address->getAddress(), $email->getTo());
        self::assertSame(['foo@lier', 'am@zing.yp'], $recipients);
        self::assertEmpty($email->getCc());
        self::assertEmpty($email->getBcc());

        $orders = $email->getContext()['orders'];
        self::assertSame($order, $orders[0]);
    }

    public function testAddingSeveralOrderCorrectlyComputeAnEmail()
    {
        $mailerProphecy = $this->prophesize(MailerInterface::class);
        $mailer = new OrdersPoolMailer($mailerProphecy->reveal());

        $order42_1 = (new Order())->setBaanCustomerNumber('foo');
        $order42_2 = (new Order())->setBaanCustomerNumber('bar');
        $order1984_1 = (new Order())->setBaanCustomerNumber('baz');

        $mailer->addOrder($order42_1, [(new People())->setEmail('foo@lier')], '42');
        $mailer->addOrder($order1984_1, [(new People())->setEmail('am@zing.yp')], '1984');
        $mailer->addOrder($order42_2, [(new People())->setEmail('foo@lier')], '42');

        self::assertSame(2, $mailer->countEmails());
        self::assertInstanceOf(TemplatedEmail::class, $email = $mailer->getEmail('42'));
        $recipients = array_map(static fn (Address $address) => $address->getAddress(), $email->getTo());
        self::assertSame(['foo@lier'], $recipients);

        $orders = $email->getContext()['orders'];
        self::assertSame($order42_1, $orders[0]);
        self::assertSame($order42_2, $orders[1]);

        $email = $mailer->getEmail('1984');

        $recipients = array_map(static fn (Address $address) => $address->getAddress(), $email->getTo());
        self::assertSame(['am@zing.yp'], $recipients);
        $orders = $email->getContext()['orders'];
        self::assertSame($order1984_1, $orders[0]);
    }
}
