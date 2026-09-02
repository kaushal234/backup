<?php

declare(strict_types=1);

namespace App\AI\Factory\Service;

use App\AI\Dto\Common\AirportModel;
use App\AI\Dto\Sales\CustomerModel;
use App\AI\Dto\Service\TechnicianOnCall\SurveyModel;
use App\AI\Dto\Service\TechnicianOnCallModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\Common\Airport;
use App\Entity\Sales\Customer;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use App\Entity\Service\TechnicianOnCallTag;

final readonly class TechnicianOnCallModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
        private EquipmentRecordModelFactory $equipmentRecordModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return TechnicianOnCall::class === $class;
    }

    /**
     * @param TechnicianOnCall $entity
     */
    public function create(object $entity): TechnicianOnCallModel
    {
        return new TechnicianOnCallModel(
            id: (int) $entity->getId(),
            legacyId: $entity->getLegacyId(),
            status: $entity->status,
            title: $entity->title,
            description: $entity->description,
            createdAt: $entity->createdAt,
            solvedAt: $entity->solvedAt,
            updatedAt: $entity->updatedAt,
            createdBy: null === $entity->createdBy ? null : $this->peopleModelFactory->create($entity->createdBy),
            technician: null === $entity->technician ? null : $this->peopleModelFactory->create($entity->technician),
            assignee: null === $entity->assignee ? null : $this->peopleModelFactory->create($entity->assignee),
            mainContact: null === $entity->getMainContact() ? null : $this->peopleModelFactory->create($entity->getMainContact()),
            customer: null === $entity->customer ? null : $this->createCustomer($entity->customer),
            equipmentRecord: null === $entity->equipmentRecord ? null : $this->equipmentRecordModelFactory->create($entity->equipmentRecord),
            serialNumber: $entity->serialNumber,
            serviceOrganizationLocation: null === $entity->salesOrganisationService ? null : $this->locationModelFactory->create($entity->salesOrganisationService),
            airport: isset($entity->airport) ? $this->createAirport($entity->airport) : null,
            indiceFactor: $entity->indiceFactor,
            unitOperationalStatus: $entity->unitOperationalStatus?->getName(),
            type: isset($entity->technicianOnCallType) ? $entity->technicianOnCallType->name : null,
            activityType: $entity->serviceActivity->name,
            errorCodes: $entity->errorCodes,
            symptoms: $entity->symptoms,
            rootCause: $entity->rootCause,
            solution: $entity->solution,
            thirdPartyName: $entity->thirdPartyName,
            thirdPartyRef: $entity->thirdPartyRef,
            thirdPartyHours: $entity->thirdPartyHours,
            thirdPartyJobDescription: $entity->thirdPartyJobDescription,
            factoryFlag: $entity->factoryFlag,
            confidential: $entity->confidential,
            warrantyLegacyId: $entity->warrantyLegacyId,
            hourMeter: $entity->hourMeter,
            tags: array_values(array_map(
                static fn (TechnicianOnCallTag $tag): string => $tag->getName(),
                $entity->getTags()->toArray(),
            )),
            survey: null === $entity->survey ? null : $this->createSurvey($entity->survey),
        );
    }

    private function createCustomer(Customer $customer): CustomerModel
    {
        return new CustomerModel(
            name: $customer->getName(),
            status: $customer->getStatus(),
        );
    }

    private function createSurvey(TechnicianOnCallSurvey $survey): SurveyModel
    {
        return new SurveyModel(
            execution: $survey->execution,
            responsiveness: $survey->responsiveness,
            communication: $survey->communication,
            attitude: $survey->attitude,
            comment: $survey->comment,
        );
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
}
