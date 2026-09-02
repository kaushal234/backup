<?php

declare(strict_types=1);

namespace App\Notifier;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

#[AsDecorator(decorates: MailerInterface::class)]
class Mailer implements MailerInterface
{
    public function __construct(
        #[AutowireDecorated] private $inner,
    ) {
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        if (!$message instanceof Email) {
            return;
        }
        $this->inner->send(FromAddressFixer::fix($message), $envelope);
    }
}
