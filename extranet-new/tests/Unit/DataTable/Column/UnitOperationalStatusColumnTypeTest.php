<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataTable\Column;

use App\DataTable\Column\LabelColumnType;
use App\DataTable\Column\UnitOperationalStatusColumnType;
use App\Sdk\Resource\Airport;
use App\Sdk\Resource\Customer;
use App\Sdk\Resource\Location;
use App\Sdk\Resource\ServiceActivity;
use App\Sdk\Resource\TechnicianOnCall;
use App\Sdk\Resource\TechnicianOnCallType;
use App\Sdk\Resource\UnitOperationalStatus;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnTypeInterface;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Test\Column\Type\ColumnTypeTestCase;

/**
 * @group unit
 */
class UnitOperationalStatusColumnTypeTest extends ColumnTypeTestCase
{
    public function test(): void
    {
        $column = $this->createNamedColumn('unitOperationalStatus');

        $technicianOnCall = $this->createTechnicianOnCall(new UnitOperationalStatus(
            iri: '/unit_operationalstatus/42',
            id: 42,
            name: 'MCF',
            description: 'desc',
        ));

        $valueView = $this->createColumnValueView($column, rowData: $technicianOnCall);

        $this->assertSame('MCF - desc', $valueView->vars['value']);
        $this->assertSame('success', $valueView->vars['label_classes']['MCF - desc']);
    }

    protected function getTestedColumnType(): ColumnTypeInterface
    {
        return new UnitOperationalStatusColumnType();
    }

    protected function getAdditionalColumnTypes(): array
    {
        return [
            new LabelColumnType(),
            new TemplateColumnType(),
            new ColumnType(),
        ];
    }

    private function createTechnicianOnCall(UnitOperationalStatus $unitOperationalStatus): TechnicianOnCall
    {
        return new TechnicianOnCall(
            iri: '/technician_on_calls/1',
            id: 1,
            confidential: false,
            title: 'title',
            description: 'description',
            status: 'open',
            createdAt: new \DateTime(),
            technicianOnCallType: new TechnicianOnCallType('/technician_on_call_types/1', 1, 'type', 'desc'),
            serviceActivity: new ServiceActivity('/service_activities/1', 1, 'activity', 'desc'),
            indiceFactor: 'IF 1',
            airport: new Airport('/airports/1', 1, 'CDG'),
            salesOrganisationService: new Location('/locations/1', 'location'),
            factoryFlag: false,
            customer: new Customer('/customers/1', 'customer'),
            openDays: 0,
            daysWithoutActivity: 0,
            daysWithoutActivityStatus: 'ok',
            originalTitle: null,
            originalDescription: null,
            unitOperationalStatus: $unitOperationalStatus,
        );
    }
}
