<?php

declare(strict_types=1);

namespace App\Tests\Mailer;

use App\Mailer\AbstractPoolMailer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class AbstractMailerTest extends TestCase
{
    use ProphecyTrait;

    public function testAddEmail()
    {
        $mailerProphecy = $this->prophesize(MailerInterface::class);

        $mailer = new class($mailerProphecy->reveal()) extends AbstractPoolMailer {
        };
        self::assertSame(0, $mailer->countEmails());

        $email = new TemplatedEmail();
        $mailer->addEmail($email);
        self::assertSame(1, $mailer->countEmails());
        self::assertSame($email, $mailer->getEmail(0));
        $mailer->addEmail($email, 'foo');
        self::assertSame($email, $mailer->getEmail('foo'));
        $mailer->addEmail(new TemplatedEmail(), 'foo');
        self::assertNotSame($email, $mailer->getEmail('foo'), 'We should be able to override a given key');
        self::assertSame(2, $mailer->countEmails());
    }

    public function mailerOptionProvider()
    {
        yield 'subject only' => [];
        yield 'with template' => ['template', []];
        yield 'with metadata' => [null, ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3']];
        yield 'without email' => [null, [], 0];
        yield 'multiple email' => ['template', ['key1' => 'value1', 'key2' => 'value2'], 5];
    }

    /**
     * @dataProvider mailerOptionProvider
     */
    public function testSend(?string $template = null, array $metadata = [], $emailNumber = 1)
    {
        $email = new TemplatedEmail();
        $mailerProphecy = $this->prophesize(MailerInterface::class);
        $mailerProphecy->send($email)->shouldBeCalledTimes($emailNumber);

        $mailer = new class($mailerProphecy->reveal()) extends AbstractPoolMailer {
        };
        for ($i = 0; $i < $emailNumber; ++$i) {
            $mailer->addEmail($email);
        }

        self::assertSame($emailNumber, $mailer->countEmails());
        $mailer->send('subject', $template, $metadata);
        self::assertSame(0, $mailer->countEmails());
    }
}
