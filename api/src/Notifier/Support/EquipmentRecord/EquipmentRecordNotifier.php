<?php

declare(strict_types=1);

namespace App\Notifier\Support\EquipmentRecord;

use App\Entity\Directory\Department;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EquipmentRecordNotifier
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly RecipientsFinder $recipientsFinder,
        private readonly MailerInterface $mailer,
        private readonly PeopleRepository $peopleRepository,
        private readonly LocationRepository $locationRepository,
    ) {
    }

    public function sendGreenTagDateUpdateEmail(EquipmentRecord $equipmentRecord, ?string $previousGreenTagDate = null): void
    {
        $recipients = $this->recipientsFinder->findRecipients($equipmentRecord);
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('manufacturing.equipment_record.green_tag_date.subject')
            ->htmlTemplate('Emails/Support/EquipmentRecord/new_green_tag_date.html.twig')
            ->context($this->buildContext($equipmentRecord) + ['previousGreenTagDate' => (null === $previousGreenTagDate ? '0000-00-00' : $previousGreenTagDate)]);

        $this->mailer->send($email);
    }

    public function sendEstimatedGreenTagDateEmail(EquipmentRecord $equipmentRecord, ?string $previousEstimatedGreenTagDate = null): void
    {
        $recipients = [
            ...$this->peopleRepository->findGroupsMembers(['ROLE_COO', 'ROLE_PSM', 'ROLE_PSA', 'ROLE_PSE', 'ROLE_PM', 'ROLE_QA', 'ROLE_QE'], $equipmentRecord->getManufacturerLocation()),
            ...$this->peopleRepository->findGroupsMembers(['ROLE_SAM', 'ROLE_SA', 'ROLE_EVP'], $equipmentRecord->getSalesOrganisation()),
        ];

        if (null !== $equipmentRecord->getBuyer() && null !== $equipmentRecord->getBuyer()->getMainSalesRepresentative()) {
            $recipients[] = $equipmentRecord->getBuyer()->getMainSalesRepresentative()->asm;
        }
        if (null !== $equipmentRecord->getEndUser() && null !== $equipmentRecord->getEndUser()->getMainSalesRepresentative()) {
            $recipients[] = $equipmentRecord->getEndUser()->getMainSalesRepresentative()->asm;
        }

        /** @var People $people */
        foreach ($recipients as $key => $people) {
            if (Department::SPARE_PARTS === $people->getDepartment()?->getName()) {
                unset($recipients[$key]);
            }
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('manufacturing.equipment_record.estimated_green_tag_date.subject')
            ->htmlTemplate('Emails/Support/EquipmentRecord/new_estimated_green_tag_date.html.twig')
            ->context($this->buildContext($equipmentRecord) + ['previousEstimatedGreenTagDate' => (null === $previousEstimatedGreenTagDate ? '0000-00-00' : $previousEstimatedGreenTagDate)]);

        if ($asmEMail = $equipmentRecord->orderFactory?->orderLine?->order?->getAsm()?->getEmail()) {
            $email->cc($asmEMail);
        }

        $this->mailer->send($email);
    }

    public function sendAlertEstimatedGreenTagDateGap(EquipmentRecord $equipmentRecord, string $previousEstimatedGreenTagDate): void
    {
        $recipients = $this->recipientsFinder->getRecipientsForAlertEstimatedGreenTagDate($equipmentRecord, $equipmentRecord->getManufacturerLocation())['recipients'];
        $inCopy = $this->recipientsFinder->getRecipientsForAlertEstimatedGreenTagDate($equipmentRecord, $equipmentRecord->getManufacturerLocation())['inCopy'];

        $gap = (new \DateTime($this->buildContext($equipmentRecord)['equipmentRecord']['estimatedGreenTagDate']))->diff(new \DateTime($previousEstimatedGreenTagDate))->format('%a');

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipients) => $recipients->getEmail(), $recipients))
            ->cc(...array_map(static fn (People $inCopy) => $inCopy->getEmail(), $inCopy))
            ->subject('manufacturing.equipment_record.alert_gap_estimated_green_tag_date.subject')
            ->htmlTemplate('Emails/Support/EquipmentRecord/alert_updated_green_tag_date.html.twig')
            ->context($this->buildContext($equipmentRecord) + ['previousEstimatedGreenTagDate' => $previousEstimatedGreenTagDate, 'gap' => $gap]);

        $this->mailer->send($email);
    }

    public function sendYellowTagDateEmail(EquipmentRecord $equipmentRecord, ?string $previousYellowTagDate = null): void
    {
        $recipients = $this->recipientsFinder->findRecipients($equipmentRecord);
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('manufacturing.equipment_record.yellow_tag_date.subject')
            ->htmlTemplate('Emails/Support/EquipmentRecord/new_yellow_tag_date.html.twig')
            ->context($this->buildContext($equipmentRecord) + ['previousYellowTagDate' => (null === $previousYellowTagDate ? '0000-00-00' : $previousYellowTagDate)]);

        $this->mailer->send($email);
    }

    public function sendAsmEstimatedGreenTagDatePeriodicalEmail(string $to, array $equipmentRecords, string $days): void
    {
        $equipmentRecords = $this->processEquipmentRecordsDates($equipmentRecords);

        $asm = $this->peopleRepository->findBy(['email' => $to, 'hidden' => false]);

        $ccs = [];
        if (null !== ($asm[0] ?? null)) {
            $location = $asm[0]->getBusinessUnit()->getLocation();
            $ccs = [...$this->peopleRepository->findGroupsMembers(['ROLE_EVP', 'ROLE_SA', 'ROLE_SAM'], $location)];
            if ('7' === $days) {
                $ccs = [...$ccs, ...$this->peopleRepository->findGroupsMembers(['ROLE_CFO', 'ROLE_RCEO'], $location)];
            }
        }

        $email = (new TemplatedEmail())
            ->to($to)
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $ccs))
            ->subject('manufacturing.equipment_record.periodical_estimated_green_tag_date.subject')
            ->htmlTemplate('Emails/Support/EquipmentRecord/periodical_estimated_green_tag_date.html.twig')
            ->context([
                'equipmentRecords' => $equipmentRecords,
                'periodicity' => '1' === $days ? 'Daily' : 'Weekly',
                'today' => (new \DateTime())->format('Y-m-d'),
                'public' => 'ASM',
            ]);

        $this->mailer->send($email);
    }

    public function sendLocationEstimatedGreenTagDatePeriodicalEmail(string $locationName, array $equipmentRecords, string $days): void
    {
        $location = $this->locationRepository->findOneBy(['name' => $locationName]);
        $recipients = [...$this->peopleRepository->findGroupsMembers(['ROLE_PSM', 'ROLE_PSA', 'ROLE_PSE', 'ROLE_COO', 'ROLE_PM'], $location)];
        $ccs = [];
        if ('7' === $days) {
            $recipients = [...$recipients, ...$this->peopleRepository->findGroupsMembers(['ROLE_RCOO', 'ROLE_RCEO'], $location)];
        } else {
            foreach ($equipmentRecords as $equipmentRecord) {
                if ($equipmentRecord['equipmentRecord']->orderFactory?->orderLine?->order?->getAsm()) {
                    $ccs[] = $equipmentRecord['equipmentRecord']->orderFactory->orderLine->order->getAsm();
                }
            }
        }
        $equipmentRecords = $this->processEquipmentRecordsDates($equipmentRecords);

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->cc(...array_map(static fn (People $inCopy) => $inCopy->getEmail(), $ccs))
            ->subject('manufacturing.equipment_record.periodical_estimated_green_tag_date.subject')
            ->htmlTemplate('Emails/Support/EquipmentRecord/periodical_estimated_green_tag_date.html.twig')
            ->context([
                'equipmentRecords' => $equipmentRecords,
                'periodicity' => '1' === $days ? 'Daily' : 'Weekly',
                'today' => (new \DateTime())->format('Y-m-d'),
                'public' => 'Factory',
            ]);

        $this->mailer->send($email);
    }

    public function sendShippedDateNotification(EquipmentRecord $equipmentRecord, ?string $previousShippedDate = null): void
    {
        $recipients = $this->recipientsFinder->getRecipientsForShippedDateUpdate($equipmentRecord);
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('manufacturing.equipment_record.shipped_date.subject')
            ->htmlTemplate('Emails/Support/EquipmentRecord/new_shipped_date.html.twig')
            ->context($this->buildContext($equipmentRecord) + ['previousShippedDate' => (null === $previousShippedDate ? '0000-00-00' : $previousShippedDate)]);

        $this->mailer->send($email);
    }

    private function processEquipmentRecordsDates(array $equipmentRecords): array
    {
        foreach ($equipmentRecords as &$equipmentRecord) {
            $equipmentRecord['equipmentRecord'] = $this->normalizer->normalize($equipmentRecord['equipmentRecord'], null, ['groups' => ['odp:view', 'equipment_shipping_record_line:detail', 'equipment_record_notification', 'equipment_record_detail', 'location_public', 'expose_legacy', 'order_factory', 'order_line', 'sales_order']]);
            $equipmentRecord['changeSet']['gapGreenTag'] = 'N/A';
            $equipmentRecord['equipmentRecord']['orderFactory']['gapPromiseGreenTag'] = 'N/A';

            if (null === ($newEstimatedGreenTagDate = ($equipmentRecord['changeSet']['estimatedGreenTagDate'][1] ?? null))) {
                continue;
            }

            $initialGreenTagDate = $equipmentRecord['changeSet']['estimatedGreenTagDate'][0] ?? null;
            if (null !== $initialGreenTagDate) {
                $equipmentRecord['changeSet']['gapGreenTag'] = (int) (new \DateTime($initialGreenTagDate))->diff(new \DateTime($newEstimatedGreenTagDate))->format('%R%a');
            }

            $promiseGreenTagDate = $equipmentRecord['equipmentRecord']['orderFactory']['factoryPromisedDeliveryDate'] ?? null;
            if (null !== $promiseGreenTagDate) {
                $equipmentRecord['equipmentRecord']['orderFactory']['gapPromiseGreenTag'] = (int) (new \DateTime($promiseGreenTagDate))->diff(new \DateTime($newEstimatedGreenTagDate))->format('%R%a');
            }
        }

        return $equipmentRecords;
    }

    private function buildContext(EquipmentRecord $equipmentRecord): array
    {
        $equipmentRecordNormalized = $this->normalizer->normalize($equipmentRecord, null, ['groups' => ['equipment_shipping_record_line:detail', 'equipment_record_notification', 'odp:view', 'equipment_record:fms_contract', 'equipment_record_detail', 'order_factory', 'order_line', 'customer_list', 'location_public', 'expose_legacy']]);

        return [
            'equipmentRecord' => $equipmentRecordNormalized,
            'sorId' => null === $equipmentRecord->getOrder() ? 'not Found' : $equipmentRecord->getOrder()->getLegacyId(),
            'solId' => null !== $equipmentRecord->orderFactory ? $equipmentRecord->orderFactory->orderLine->getLegacyId() : 'Not found',
            'serialNumber' => $equipmentRecord->getSerialNumber(),
            'endUserName' => $equipmentRecordNormalized['endUser']['name'] ?? 'not found',
            'factory' => $equipmentRecordNormalized['manufacturerLocation']['name'] ?? 'not found',
        ];
    }
}
