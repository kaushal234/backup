<?php

declare(strict_types=1);

namespace App\Notifier\Sales;

use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class CustomerServiceRecordNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly PeopleRepository $peopleRepository,
    ) {
    }

    public function sendTechnicianEmail(array $csr, string $newDate): void
    {
        /** @var People|null $technician */
        $technician = $this->peopleRepository->findOneBy(['legacyId' => $csr['tech_id']]);
        if (null === $technician) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($technician->getEmail())
            ->subject('csr.subject')
            ->htmlTemplate('Emails/Sales/Customer/csr_technician_notification.html.twig')
            ->context($csr + ['newDate' => $newDate]);

        $this->mailer->send($email);
    }
}
