<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\MessageListener;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testOnMessageWithTemplatedEmail(): void
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $context = ['foo' => 'bar', 'baz' => 1, 'date' => new \DateTime(), 'locale' => 'it'];
        $message = (new TemplatedEmail())
            ->to('yolo@yo.lo')
            ->subject('to_be_translated')
            ->context($context)
        ;
        $envelopeProphecy = $this->prophesize(Envelope::class);
        $event = new MessageEvent($message, $envelopeProphecy->reveal(), 'foo');

        $parameterProphecy = $this->prophesize(ParameterBagInterface::class);
        $serviceLocatorProphecy->get(ParameterBagInterface::class)->shouldBeCalledTimes(1)->willReturn($parameterProphecy->reveal());

        $parameterProphecy->get('notify_mail_sender')->shouldBeCalledTimes(1)->willReturn('noreply@tld-gse.com');

        $translatorProphecy = $this->prophesize(TranslatorInterface::class);
        $serviceLocatorProphecy->get(TranslatorInterface::class)->shouldBeCalledTimes(1)->willReturn($translatorProphecy->reveal());
        $translatorProphecy->trans('to_be_translated', ['%foo%' => 'bar', '%baz%' => '1', '%locale%' => 'it'], 'emails', 'it')
            ->willReturn('translated')
            ->shouldBeCalledTimes(1);

        $loggerProphecy = $this->prophesize(LoggerInterface::class);
        $serviceLocatorProphecy->get('monolog.logger.emails')->shouldBeCalledTimes(1)->willReturn($loggerProphecy->reveal());
        $loggerProphecy->info('Email "translated" has been sent to yolo@yo.lo from noreply@tld-gse.com')->shouldBeCalledTimes(1);

        $listener = new MessageListener($serviceLocatorProphecy->reveal());
        $listener->onMessage($event);

        self::assertSame('translated', $message->getSubject());
        self::assertSame($context, $message->getContext());

        self::assertCount(1, $message->getFrom());
        /** @var Address $sender */
        $sender = $message->getFrom()[0];
        self::assertInstanceOf(Address::class, $sender);
        self::assertSame('noreply@tld-gse.com', $sender->getAddress());
    }

    public function testOnMessageWithEmail(): void
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $message = (new Email())
            ->from('foob@ar.buz')
            ->to('yolo@yo.lo', 'fabien@symfony.com')
            ->cc('bec@use')
            ->bcc('yo@nn.pelette')
            ->subject('will_not_be_translated');

        $envelopeProphecy = $this->prophesize(Envelope::class);
        $event = new MessageEvent($message, $envelopeProphecy->reveal(), 'foo');

        $translatorProphecy = $this->prophesize(TranslatorInterface::class);
        $serviceLocatorProphecy->get(TranslatorInterface::class)->shouldNotBeCalled();
        $translatorProphecy->trans(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $loggerProphecy = $this->prophesize(LoggerInterface::class);
        $serviceLocatorProphecy->get('monolog.logger.emails')->shouldBeCalledTimes(1)->willReturn($loggerProphecy->reveal());
        $loggerProphecy->info('Email "will_not_be_translated" has been sent to yolo@yo.lo, fabien@symfony.com from foob@ar.buz, copied to bec@use, blind copied to yo@nn.pelette')
            ->shouldBeCalledTimes(1);

        $listener = new MessageListener($serviceLocatorProphecy->reveal());
        $listener->onMessage($event);

        self::assertCount(1, $message->getFrom());
        /** @var Address $sender */
        $sender = $message->getFrom()[0];
        self::assertInstanceOf(Address::class, $sender);
        self::assertSame('foob@ar.buz', $sender->getAddress());
    }
}
