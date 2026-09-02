<?php

declare(strict_types=1);

namespace App\Formatter\Spreadsheet\Service;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Formatter\Spreadsheet\AbstractSpreadsheetFormatter;

class CustomerServiceRecordSpreadsheetFormatter extends AbstractSpreadsheetFormatter
{
    public function getColumnToRename(): array
    {
        return [
            'openIntervention.leader' => 'intervention leader',
            'airport.code' => 'airport',
            'equipmentRecord.serialNumber' => 'serial number',
            'equipmentRecord.model' => 'model',
            'equipmentRecord.manufacturerLocation.name' => 'factory',
            'equipmentRecord.salesOrganisation.name' => 'sso',
            'equipmentRecord.buyer.name' => 'buyer',
            'equipmentRecord.endUser.name' => 'end user',
            'equipmentRecord.customerSerialNumber' => 'customer asset',
            'legacyModuleName' => 'csr type',
            'legacyModuleId' => 'module legacy id',
        ];
    }

    public function getComputedColumns(): array
    {
        return ['interventionStatus', 'interventionPlannedAt', 'type'];
    }

    /**
     * @param AbstractCustomerServiceRecord $item
     */
    public function computeColumn(object $item, string $column): mixed
    {
        $interventions = $item->getInterventions();
        switch ($column) {
            case 'interventionStatus':
                $value = !$interventions->isEmpty() ? $interventions->last()->getStatus() : null;
                break;
            case 'interventionPlannedAt':
                $value = !$interventions->isEmpty() ? $interventions->last()->plannedAt : null;
                break;
            case 'type':
                $reflection = new \ReflectionClass(AbstractCustomerServiceRecord::class);
                $attributes = $reflection->getAttributes('Doctrine\ORM\Mapping\DiscriminatorMap')[0];
                $value = array_search($item::class, $attributes->getArguments()[0], true);
                break;
            default:
                $value = null;
        }

        return $value;
    }

    public function supports(string $class, string $operationName): bool
    {
        return AbstractCustomerServiceRecord::class === $class;
    }
}
