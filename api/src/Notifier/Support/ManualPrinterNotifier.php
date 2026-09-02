<?php

declare(strict_types=1);

namespace App\Notifier\Support;

use App\Entity\Support\ManualPrint;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ManualPrinterNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function sendEmail(ManualPrint $manualPrint, array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->to($manualPrint->manualPrinter->email)
            ->cc($manualPrint->createdBy->getEmail())
            ->subject('print.subject_new')
            ->htmlTemplate('Emails/Support/new_manual_print.html.twig')
            ->context($this->buildContext($manualPrint, $context));

        $this->mailer->send($email);
    }

    private function buildContext(ManualPrint $manualPrint, array $context = []): array
    {
        return $context + [
            'manualPrint' => $manualPrint,
            'print_normalized' => $this->normalizer->normalize($manualPrint, null, [
                'groups' => [
                    'manual_print',
                    'manual',
                    'equipment_record_manuals',
                    'equipment_record_detail',
                ],
            ]),
        ];
    }
}
