<?php

declare(strict_types=1);

namespace App\DeletionVoter\Service;

use App\DeletionVoter\DeletionVoterInterface;
use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Service\TechnicianOnCall;
use LegacyBundle\Manager\ModLinkManager;
use LegacyBundle\Repository\WarrantyClaimRepository;

class TechnicianOnCallDeletionVoter implements DeletionVoterInterface
{
    public function __construct(
        private readonly ModLinkManager $modLinkManager,
        private readonly WarrantyClaimRepository $warrantyClaimRepository,
    ) {
    }

    public function supports($entity): bool
    {
        return $entity instanceof TechnicianOnCall;
    }

    /**
     * @param TechnicianOnCall $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('Technician On Call');
        $reason->setLabel((string) $entity->getId());

        if (!$entity->getCustomerServiceRecords()->isEmpty()) {
            return $reason
                ->setIdentifiers($entity->getCustomerServiceRecords()->map(static fn ($csr) => $csr->getId())->toArray())
                ->setCountedType('Customer Service Record');
        }

        if (!$entity->getSparePartsRequests()->isEmpty()) {
            return $reason
                ->setIdentifiers($entity->getSparePartsRequests()->map(static fn ($spr) => $spr->getId())->toArray())
                ->setCountedType('Spare Parts Request');
        }

        $tocLegacyId = $entity->getLegacyId();
        if (null === $tocLegacyId) {
            return null;
        }

        $pdcIds = $this->getLinkedPdcIds($tocLegacyId);
        if (!empty($pdcIds)) {
            return $reason
                ->setIdentifiers($pdcIds)
                ->setCountedType('Product Demerit Claim');
        }

        $wcLinks = $this->modLinkManager->getLinks(module: TechnicianOnCall::MODULE_NAME, moduleId: $tocLegacyId, type: 'WC');
        if (empty($wcLinks)) {
            return null;
        }

        $wcIds = array_column($wcLinks, 'item');
        $nonPendingWcIds = $this->warrantyClaimRepository->findNonPendingIds($wcIds);

        if (!empty($nonPendingWcIds)) {
            return $reason
                ->setIdentifiers($nonPendingWcIds)
                ->setCountedType('Warranty Claim');
        }

        return null;
    }

    private function getLinkedPdcIds(int $technicianOnCallLegacyId): array
    {
        $linksAsModule = $this->modLinkManager->getLinks(module: 'PDC', type: TechnicianOnCall::MODULE_NAME, typeId: $technicianOnCallLegacyId);
        $linksAsType = $this->modLinkManager->getLinks(module: TechnicianOnCall::MODULE_NAME, moduleId: $technicianOnCallLegacyId, type: 'PDC');

        return array_unique(array_merge(
            array_column($linksAsModule, 'parent_id'),
            array_column($linksAsType, 'item'),
        ));
    }
}
