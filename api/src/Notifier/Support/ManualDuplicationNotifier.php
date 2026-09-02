<?php

declare(strict_types=1);

namespace App\Notifier\Support;

use App\Entity\Support\Manual;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class ManualDuplicationNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer
    ) {
    }

    public function sendEmail(string $to, Manual $manual, array $errors, array $duplicatedManuals): void
    {
        $email = (new TemplatedEmail())
            ->to($to)
            ->subject('manual.subject_duplication')
            ->htmlTemplate('Emails/Support/new_manual_duplication.html.twig')
            ->context(['manual' => $manual, 'errors' => $errors, 'duplicatedManuals' => $duplicatedManuals]);

        $this->mailer->send($email);
    }
}
