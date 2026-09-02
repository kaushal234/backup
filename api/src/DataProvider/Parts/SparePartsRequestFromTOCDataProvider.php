<?php

declare(strict_types=1);

namespace App\DataProvider\Parts;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Parts\SparePartsRequestFromTOC;
use App\Entity\Parts\TOCSparePartsRequest;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Entity\Service\TechnicianOnCall;
use App\Repository\Parts\TOCSparePartsRequestRepository;
use App\Repository\Sales\CustomerRelationshipTeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class SparePartsRequestFromTOCDataProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private IriConverterInterface $iriConverter,
        private PropertyAccessorInterface $propertyAccessor,
        private TranslatorInterface $translator,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $technicianOnCallRepository = $this->entityManager->getRepository(TechnicianOnCall::class);
        $technicianOnCall = $technicianOnCallRepository->find($uriVariables['id']);

        if (null === $technicianOnCall) {
            throw new BadRequestHttpException('TOC not found.');
        }

        if (null === ($equipmentRecord = $technicianOnCall->equipmentRecord)) {
            throw new BadRequestHttpException(\sprintf('No Equipment Record found for TOC#%s', $technicianOnCall->getId()));
        }

        foreach (['endUser' => 'end_user', 'salesOrganisation' => 'sales_organisation', 'manufacturerLocation' => 'manufacturer_location'] as $requiredProperty => $translationKey) {
            if (null === $this->propertyAccessor->getValue($equipmentRecord, $requiredProperty)) {
                throw new BadRequestHttpException($this->translator->trans('spare_parts_request.errors.er_no_'.$translationKey, [], 'spare_parts_request'));
            }
        }

        /** @var CustomerRelationshipTeamRepository $customerRelationshipTeamRepository */
        $customerRelationshipTeamRepository = $this->entityManager->getRepository(CustomerRelationshipTeam::class);

        $sparePartsHub = null;
        foreach ($customerRelationshipTeamRepository->findCustomerRelationshipTeamsForEquipmentRecord($equipmentRecord) as $customerRelationshipTeam) {
            if (null !== $customerRelationshipTeam->getPartsLocation()) {
                $sparePartsHub = $customerRelationshipTeam->getPartsLocation();
            }
        }

        if (null === $sparePartsHub) {
            throw new BadRequestHttpException($this->translator->trans('spare_parts_request.errors.invalid_sph', ['%customer%' => $equipmentRecord->getEndUser()->getName()], 'spare_parts_request'));
        }

        $customers = [];
        foreach ([$equipmentRecord->getEndUser(), $equipmentRecord->getMaintainer(), $equipmentRecord->getBuyer()] as $customer) {
            if (null !== $customer) {
                $customers[] = $this->iriConverter->getIriFromResource($customer);
            }
        }

        /** @var TOCSparePartsRequestRepository $sparePartsRequestRepository */
        $sparePartsRequestRepository = $this->entityManager->getRepository(TOCSparePartsRequest::class);

        return new SparePartsRequestFromTOC(
            customer: $this->iriConverter->getIriFromResource($equipmentRecord->getEndUser()),
            sso: $this->iriConverter->getIriFromResource($equipmentRecord->getSalesOrganisation()),
            factory: $this->iriConverter->getIriFromResource($equipmentRecord->getManufacturerLocation()),
            erpLocation: $this->iriConverter->getIriFromResource($equipmentRecord->getManufacturerLocation()),
            sph: $this->iriConverter->getIriFromResource($sparePartsHub),
            equipmentRecords: [$this->iriConverter->getIriFromResource($equipmentRecord)],
            customers: array_unique($customers),
            airport: null !== $equipmentRecord->getAirport() ? $this->iriConverter->getIriFromResource($equipmentRecord->getAirport()) : null,
            sparePartsRequest: $sparePartsRequestRepository->findSparePartsRequestForToc($technicianOnCall->getId())
        );
    }
}
