<?php

declare(strict_types=1);

namespace App\Notifier\Directory;

use App\Entity\Directory\People;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class PeopleExtranetAccessNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer
    ) {
    }

    public function sendEmail(People $people): void
    {
        $supervisor = $people->getSupervisor();
        if (!$supervisor instanceof People) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($people->getEmail())
            ->addBcc($supervisor->getEmail())
            ->subject('people.extranet_access.subject')
            ->htmlTemplate('Emails/Directory/peopleExtranetAccessError.html.twig')
            ->context(['people' => (string) $people]);

        $this->mailer->send($email);
    }
}
