<?php

declare(strict_types=1);

namespace App\Notifier\User;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserPasswordNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function sendConfirmation(User $user, string $token): void
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('password.reset_confirmation.subject')
            ->htmlTemplate('Emails/User/reset_confirmation.html.twig')
            ->context($this->buildContext($user) + ['resetToken' => $token]);

        $this->mailer->send($email);
    }

    public function sendPasswordChanged(User $user): void
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('password.changed.subject')
            ->htmlTemplate('Emails/User/password_changed.html.twig')
            ->context($this->buildContext($user));

        $this->mailer->send($email);
    }

    private function buildContext(User $user): array
    {
        return ['user' => $this->normalizer->normalize($user, 'jsonld', ['groups' => ['user', 'user:me', 'user:reset_password']])];
    }
}
