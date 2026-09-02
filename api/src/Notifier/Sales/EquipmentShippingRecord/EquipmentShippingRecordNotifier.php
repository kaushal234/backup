<?php

declare(strict_types=1);

namespace App\Notifier\Sales\EquipmentShippingRecord;

use App\Entity\Directory\People;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EquipmentShippingRecordNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly RecipientsFinder $recipientsFinder,
    ) {
    }

    public function sendNewEquipmentShippingRecordNotification(EquipmentShippingRecord $equipmentShippingRecord): void
    {
        $recipients = $this->recipientsFinder->findForNewRecord($equipmentShippingRecord);

        if (empty($recipients)) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('equipment_shipping_record.subject.new_equipment_shipping_record')
            ->htmlTemplate('Emails/Sales/EquipmentShippingRecord/new_equipment_shipping_record_notification.html.twig')
        ->context(['equipmentShippingRecord' => $equipmentShippingRecord, 'equipmentShippingRecordId' => $equipmentShippingRecord->getId()]);

        $this->mailer->send($email);
    }

    public function onChangePickUpInformationNotification(array $pickUpChange): void
    {
        /** @var EquipmentShippingRecordLine $equipmentShippingRecordLine */
        $equipmentShippingRecordLine = $pickUpChange['equipmentShippingRecordLine'];

        $recipients = $this->recipientsFinder->findForPickUpChanges($equipmentShippingRecordLine);

        if (empty($recipients)) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('equipment_shipping_record.subject.onChangePickUpInformationNotification')
            ->htmlTemplate('Emails/Sales/EquipmentShippingRecord/on_change_pick_up_information_notification.html.twig')
            ->context([
                'equipmentShippingRecordLine' => $equipmentShippingRecordLine,
                'equipmentShippingRecordId' => $equipmentShippingRecordLine->equipmentShippingRecord->getId(),
                'changes' => $pickUpChange['changes'],
            ]);

        $this->mailer->send($email);
    }
}
