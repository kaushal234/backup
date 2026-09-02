<?php

declare(strict_types=1);

namespace App\Notifier\Legal;

use App\Entity\Directory\People;
use App\Entity\Legal\Category;
use App\Entity\Legal\Contract;
use App\Entity\Sales\AbstractSalesRepresentative;
use App\Repository\Directory\PeopleRepository;

class RecipientFinder
{
    public function __construct(
        private readonly PeopleRepository $repository,
    ) {
    }

    /**
     * @return list<People>
     */
    public function findTos(Contract $contract): array
    {
        $recipients = match ($contract->subCategory->category->name) {
            Category::CUSTOMERS => $this->findCustomerRecipients($contract),
            Category::REAL_ESTATE => $this->findRealEstateRecipients($contract),
            Category::VENDORS => $this->findVendorsAgreementsRecipients($contract),
            Category::BANK => $this->findBankingRecipients($contract),
            Category::IP_IT => $this->findITRecipients(),
            default => [],
        };

        return [
            $contract->owner,
            ...$recipients,
        ];
    }

    /**
     * @return list<People>
     */
    public function findCcs(Contract $contract): array
    {
        return null !== $contract->owner->getSupervisor() ? [$contract->owner->getSupervisor()] : [];
    }

    /**
     * @return list<People>
     */
    private function findCustomerRecipients(Contract $contract): array
    {
        $contractSubDivisionIds = [];
        foreach ($contract->getBusinessUnits() as $businessUnit) {
            $subDivision = $businessUnit->getRegion()?->getSubDivision();
            if (null !== $subDivision) {
                $contractSubDivisionIds[] = $subDivision->getId();
            }
        }

        $recipients = [];
        foreach ($contract->getCustomers() as $customer) {
            $main = $customer->getMainSalesRepresentative();
            if (null !== $main && $this->salesRepMatchesContractBusinessUnits($main, $contractSubDivisionIds)) {
                $recipients[] = $main->asm;
            }

            foreach ($customer->getSecondarySalesRepresentatives() as $secondary) {
                if ($this->salesRepMatchesContractBusinessUnits($secondary, $contractSubDivisionIds)) {
                    $recipients[] = $secondary->asm;
                }
            }
        }

        return $recipients;
    }

    /**
     * @param list<int> $contractSubDivisionIds
     */
    private function salesRepMatchesContractBusinessUnits(
        AbstractSalesRepresentative $representative,
        array $contractSubDivisionIds,
    ): bool {
        return \in_array($representative->subDivision->getId(), $contractSubDivisionIds, true);
    }

    /**
     * @return list<People>
     */
    private function findRealEstateRecipients(Contract $contract): array
    {
        $recipients = [];
        foreach ($contract->getBusinessUnits() as $businessUnit) {
            $recipients = [
                ...$recipients,
                ...$this->repository->findGroupsMembers(['ROLE_CEO', 'ROLE_COO'], $businessUnit->getLocation()),
            ];
        }

        return $recipients;
    }

    /**
     * @return list<People>
     */
    private function findVendorsAgreementsRecipients(Contract $contract): array
    {
        $recipients = [];
        foreach ($contract->getBusinessUnits() as $businessUnit) {
            $recipients = [
                ...$recipients,
                ...$this->repository->findGroupsMembers(['ROLE_MLM'], $businessUnit->getLocation()),
            ];
        }

        return [
            ...$recipients,
            ...$this->repository->findGroupsMembers(['ROLE_CPO']),
        ];
    }

    /**
     * @return list<People>
     */
    private function findBankingRecipients(Contract $contract): array
    {
        $recipients = [];
        foreach ($contract->getBusinessUnits() as $businessUnit) {
            $recipients = [
                ...$recipients,
                ...$this->repository->findGroupsMembers(['ROLE_CFO'], $businessUnit->getLocation()),
            ];
        }

        return [
            ...$recipients,
            ...$this->repository->findGroupsMembers(['ROLE_GCFO']),
        ];
    }

    /**
     * @return list<People>
     */
    private function findITRecipients(): array
    {
        return [
            ...$this->repository->findGroupsMembers(['ROLE_CIO', 'ROLE_LGS']),
        ];
    }
}
