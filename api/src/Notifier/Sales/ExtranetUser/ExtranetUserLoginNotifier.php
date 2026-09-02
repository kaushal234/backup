<?php

declare(strict_types=1);

namespace App\Notifier\Sales\ExtranetUser;

use App\Entity\Directory\People;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class ExtranetUserLoginNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly RecipientsFinder $recipientsFinder,
    ) {
    }

    public function sendEmail(array $extranetUserConnections): void
    {
        $recipients = $this->recipientsFinder->findRecipients();
        if ([] === $recipients) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('extranet_user.last_login.subject')
            ->htmlTemplate('Emails/Sales/ExtranetUser/last_login_notification.html.twig')
            ->context(['extranet_users_connections' => $extranetUserConnections]);

        $this->mailer->send($email);
    }
}
