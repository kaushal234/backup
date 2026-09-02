<?php

declare(strict_types=1);

namespace App\Notifier\Sales\PreDeliveryInspection;

use App\Entity\Directory\People;
use App\Entity\Sales\PreDeliveryInspection;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PreDeliveryInspectionNotifier
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly RecipientsFinder $recipientsFinder,
        private readonly MailerInterface $mailer,
        private readonly PeopleRepository $peopleRepository,
        private readonly LocationRepository $locationRepository
    ) {
    }

    public function sendNewPdiScheduled(PreDeliveryInspection $preDeliveryInspection): void
    {
        $recipients = $this->recipientsFinder->findRecipients($preDeliveryInspection);
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('sales.pre_delivery_inspection.scheduled.subject')
            ->htmlTemplate('Emails/Sales/PreDeliveryInspection/new_pre_delivery_inspection_scheduled.html.twig')
            ->context($this->buildContext($preDeliveryInspection));

        $this->mailer->send($email);
    }

    public function sendPdiDateChanged(PreDeliveryInspection $preDeliveryInspection, $previousDate): void
    {
        $recipients = $this->recipientsFinder->findRecipients($preDeliveryInspection);

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('sales.pre_delivery_inspection.date_changed.subject')
            ->htmlTemplate('Emails/Sales/PreDeliveryInspection/pre_delivery_inspection_date_changed.html.twig')
            ->context($this->buildContext($preDeliveryInspection) + ['previousDate' => $previousDate]);

        $this->mailer->send($email);
    }

    public function sendPdiClosed(PreDeliveryInspection $preDeliveryInspection): void
    {
        $recipients = $this->recipientsFinder->findRecipients($preDeliveryInspection);

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('sales.pre_delivery_inspection.closed.subject')
            ->htmlTemplate('Emails/Sales/PreDeliveryInspection/new_pre_delivery_inspection_ended.html.twig')
            ->context($this->buildContext($preDeliveryInspection));

        $this->mailer->send($email);
    }

    private function buildContext(PreDeliveryInspection $preDeliveryInspection): array
    {
        return [
            'preDeliveryInspection' => $preDeliveryInspection,
            'status' => $preDeliveryInspection->getStatus(),
            'sorId' => null === $preDeliveryInspection->getEquipmentRecord()->getOrder() ? 'Not Found' : $preDeliveryInspection->getEquipmentRecord()->getOrder()->getLegacyId(),
            'solId' => (null !== $preDeliveryInspection->getEquipmentRecord()->orderFactory && null !== $preDeliveryInspection->getEquipmentRecord()->orderFactory->orderLine)
                ? $preDeliveryInspection->getEquipmentRecord()->orderFactory->orderLine->getLegacyId()
                : 'Not found',
            'serialNumber' => $preDeliveryInspection->getEquipmentRecord()->getSerialNumber(),
        ];
    }
}
