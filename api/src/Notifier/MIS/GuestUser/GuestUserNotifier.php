<?php

declare(strict_types=1);

namespace App\Notifier\MIS\GuestUser;

use App\Entity\MIS\GuestUser\GuestUser;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class GuestUserNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {
    }

    public function sendDisabledNotification(GuestUser $guest): void
    {
        $supervisor = $guest->getSupervisor();
        if ('' === $supervisor->getEmail()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($supervisor->getEmail())
            ->subject('guest_user.disabled.subject')
            ->htmlTemplate('Emails/MIS/GuestUser/disabled.html.twig')
            ->context([
                'firstName' => $supervisor->getFirstname(),
                'lastName' => $supervisor->getLastname(),
                'guestName' => (string) $guest,
            ]);

        $this->mailer->send($email);
    }
}
