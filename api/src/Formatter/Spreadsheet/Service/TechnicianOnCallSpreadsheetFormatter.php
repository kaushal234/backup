<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Service;

use App\Entity\AuditLog;
use App\Entity\Service\TechnicianOnCall;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getColumnToRename(): array
    {
        return [
            'id' => $this->translator->trans('toc.excel.id', [], 'technician_on_call'),
            'createdAt' => $this->translator->trans('toc.excel.created_at', [], 'technician_on_call'),
            'salesOrganisationService.name' => $this->translator->trans('toc.excel.sales_organisation_service', [], 'technician_on_call'),
            'assignee' => $this->translator->trans('toc.excel.assignee', [], 'technician_on_call'),
            'technician' => $this->translator->trans('toc.excel.technician', [], 'technician_on_call'),
            'status' => $this->translator->trans('toc.excel.status', [], 'technician_on_call'),
            'serviceActivity.name' => $this->translator->trans('toc.excel.service_activity', [], 'technician_on_call'),
            'technicianOnCallType.name' => $this->translator->trans('toc.excel.who_pays', [], 'technician_on_call'),
            'indiceFactor' => $this->translator->trans('toc.excel.indice_factor', [], 'technician_on_call'),
            'unitOperationalStatus.name' => $this->translator->trans('toc.excel.unit_status', [], 'technician_on_call'),
            'equipmentRecord.model' => $this->translator->trans('toc.excel.model', [], 'technician_on_call'),
            'equipmentRecord.serialNumber' => $this->translator->trans('toc.excel.sn', [], 'technician_on_call'),
            'mainContact.fullName' => $this->translator->trans('toc.excel.main_contact', [], 'technician_on_call'),
            'title' => $this->translator->trans('toc.excel.title', [], 'technician_on_call'),
            'updatedAt' => $this->translator->trans('toc.excel.last_update', [], 'technician_on_call'),
            'factoryFlag' => $this->translator->trans('toc.excel.factory_flag', [], 'technician_on_call'),
            'createdBy' => $this->translator->trans('toc.excel.create_by', [], 'technician_on_call'),
            'airport.code' => $this->translator->trans('toc.excel.airport', [], 'technician_on_call'),
            'customer.name' => $this->translator->trans('toc.excel.customer', [], 'technician_on_call'),
            'openDays' => $this->translator->trans('toc.excel.open_days', [], 'technician_on_call'),
            'daysWithoutActivity' => $this->translator->trans('toc.excel.without_activity_days', [], 'technician_on_call'),
            'csrList' => $this->translator->trans('toc.excel.csr_list', [], 'technician_on_call'),
            'sparePartsRequest.count' => $this->translator->trans('toc.excel.spr_number', [], 'technician_on_call'),
            'equipmentRecord.manufacturerLocation.name' => $this->translator->trans('toc.excel.factory_name', [], 'technician_on_call'),
            'solvedAt' => $this->translator->trans('toc.excel.solved_at', [], 'technician_on_call'),
            'factoryFlagHistory' => $this->translator->trans('toc.excel.factory_flag_history', [], 'technician_on_call'),
            'hourmeter' => $this->translator->trans('toc.excel.hourmeter', [], 'technician_on_call'),
            'partsList' => $this->translator->trans('toc.excel.parts_list', [], 'technician_on_call'),
            'sprList' => $this->translator->trans('toc.excel.spr_list', [], 'technician_on_call'),
            'warrantyLegacyId' => $this->translator->trans('toc.excel.warranty_legacy_id', [], 'technician_on_call'),
            'thirdPartyName' => $this->translator->trans('toc.excel.third_party_name', [], 'technician_on_call'),
            'equipmentRecord.buyer.name' => $this->translator->trans('toc.excel.buyer', [], 'technician_on_call'),
            'equipmentRecord.endUser.name' => $this->translator->trans('toc.excel.end_user', [], 'technician_on_call'),
            'equipmentRecord.customerSerialNumber' => $this->translator->trans('toc.excel.customer_asset_number', [], 'technician_on_call'),
        ];
    }

    public function getComputedColumns(): array
    {
        return ['technicianOnCallType.name', 'csrList', 'customer.name', 'sparePartsRequest.count', 'factoryFlagHistory', 'hourmeter', 'partsList', 'sprList', 'mainContact.fullName'];
    }

    /**
     * @param TechnicianOnCall $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        return match ($column) {
            'technicianOnCallType.name' => $this->translator->trans($item->technicianOnCallType->name, [], 'technician_on_call'),
            'csrList' => (static function () use ($item) {
                return implode(', ', $item->customerServiceRecords->map(static fn ($csr) => $csr->getId())->toArray());
            })(),
            'customer.name' => (function () use ($item) {
                if (!$item->customer) {
                    return '';
                }
                $name = $item->customer->getName();
                $er = $item->equipmentRecord;
                if (!$er) {
                    return $name;
                }
                $roles = [];
                if ($er->getBuyer()?->getId() === $item->customer->getId()) {
                    $roles[] = $this->translator->trans('toc.excel.role.buyer', [], 'technician_on_call');
                }
                if ($er->getEndUser()?->getId() === $item->customer->getId()) {
                    $roles[] = $this->translator->trans('toc.excel.role.end_user', [], 'technician_on_call');
                }
                if ($er->getMaintainer()?->getId() === $item->customer->getId()) {
                    $roles[] = $this->translator->trans('toc.excel.role.maintainer', [], 'technician_on_call');
                }

                return $roles ? \sprintf('%s (%s)', $name, implode(', ', $roles)) : $name;
            })(),
            'sparePartsRequest.count' => $item->getSparePartsRequests()->count(),
            'mainContact.fullName' => (static function () use ($item) {
                $mainContact = $item->getMainContact();

                return $mainContact ? mb_trim(\sprintf('%s %s', $mainContact->getFirstname(), $mainContact->getLastname())) : '';
            })(),
            'factoryFlagHistory' => (function () use ($item) {
                return (bool) $this->entityManager->getRepository(AuditLog::class)->findBy([
                    'referenceId' => $item->getId(),
                    'auditType' => 'technician_on_call',
                    'property' => 'factoryFlag',
                ]);
            })(),
            'hourmeter' => $item->getHourMeterTransactions()->last() ? $item->getHourMeterTransactions()->last()->hourMeter : '',
            'partsList' => (static function () use ($item) {
                return implode(', ', array_unique($item->getParts()->map(static fn ($part) => $part->partNumber)->toArray()));
            })(),
            'sprList' => (static function () use ($item) {
                return implode(', ', array_unique($item->getSparePartsRequests()->map(static function ($sparePartsRequest) {
                    return implode(', ', $sparePartsRequest->getParts()->map(static fn ($sparePart) => $sparePart->partNumber)->toArray());
                })->toArray()));
            })(),
            default => parent::computeColumn($item, $column),
        };
    }

    public function supports(string $class, string $operationName): bool
    {
        return TechnicianOnCall::class === $class;
    }
}
