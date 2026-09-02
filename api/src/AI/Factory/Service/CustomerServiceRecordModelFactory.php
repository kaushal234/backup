<?php

declare(strict_types=1);

namespace App\AI\Factory\Service;

use App\AI\Dto\Common\AirportModel;
use App\AI\Dto\Service\CustomerServiceRecord\CustomerServiceRecordModel;
use App\AI\Dto\Service\CustomerServiceRecord\InterventionModel;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\Common\Airport;
use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Entity\Service\CustomerServiceRecord\ServiceBulletinCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;

final readonly class CustomerServiceRecordModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private EquipmentRecordModelFactory $equipmentRecordModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return AbstractCustomerServiceRecord::class === $class;
    }

    /**
     * @param AbstractCustomerServiceRecord $entity
     */
    public function create(object $entity): CustomerServiceRecordModel
    {
        return new CustomerServiceRecordModel(
            type: $this->resolveType($entity),
            status: $entity->getStatus(),
            title: $entity->title,
            description: $entity->description,
            createdAt: $entity->createdAt,
            updatedAt: $entity->updatedAt,
            plannedAt: $entity->plannedAt,
            completedAt: $entity->completedAt,
            closedAt: $entity->closedAt,
            createdBy: null === $entity->createdBy ? null : $this->peopleModelFactory->create($entity->createdBy),
            equipmentRecord: $this->equipmentRecordModelFactory->create($entity->equipmentRecord),
            airport: null === $entity->getAirport() ? null : $this->createAirport($entity->getAirport()),
            interventions: array_values(array_map(
                fn (Intervention $intervention) => $this->createIntervention($intervention),
                $entity->getInterventions()->toArray(),
            )),
        );
    }

    private function resolveType(AbstractCustomerServiceRecord $entity): string
    {
        return match (true) {
            $entity instanceof TechnicianOnCallCustomerServiceRecord => 'toc',
            $entity instanceof ServiceBulletinCustomerServiceRecord => 'service_bulletin',
            $entity instanceof CommissioningCustomerServiceRecord => 'commissioning',
            default => 'default',
        };
    }

    private function createAirport(Airport $airport): AirportModel
    {
        return new AirportModel(
            code: $airport->getCode(),
            type: $airport->getType(),
            cityCode3: $airport->getCityCode3(),
            cityName: $airport->getCityName(),
            state: $airport->getState(),
            country: $airport->getCountry()?->getName(),
            name: $airport->getName(),
            source: $airport->getSource(),
            latitude: $airport->getLatitude(),
            longitude: $airport->getLongitude(),
        );
    }

    private function createIntervention(Intervention $intervention): InterventionModel
    {
        return new InterventionModel(
            status: $intervention->getStatus(),
            plannedAt: $intervention->plannedAt,
            startedAt: $intervention->startedAt,
            leader: $this->peopleModelFactory->create($intervention->leader),
            plannedBy: null === $intervention->plannedBy ? null : $this->peopleModelFactory->create($intervention->plannedBy),
            operators: array_values(array_map(
                fn (People $operator) => $this->peopleModelFactory->create($operator),
                $intervention->getOperators()->toArray(),
            )),
        );
    }
}
