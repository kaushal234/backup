<?php

declare(strict_types=1);

namespace App\Notifier\Sales\Customer;

use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerType;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;

class RecipientsFinder
{
    public function __construct(
        private readonly PeopleRepository $peopleRepository,
        private readonly LocationRepository $locationRepository,
    ) {
    }

    public function findRecipients(Customer $customer): array
    {
        $recipients = [];
        // Add all secondary sales representatives (ASMs)
        foreach ($customer->getSecondarySalesRepresentatives() as $secondarySalesRepresentative) {
            $recipients[] = $secondarySalesRepresentative->asm;
        }

        // Check if the customer is a third-party type
        // Third-party customers require additional legal compliance manager notifications
        if ($customer->getCustomerTypes()->filter(static fn (CustomerType $customerType) => \in_array($customerType->getName(), CustomerType::THIRD_PARTIES_NAMES, true))->count() > 0) {
            // Add legal compliance managers from ALVEST business unit
            $alvestBusinessUnit = $this->locationRepository->findOneBy(['name' => 'ALVEST']);
            foreach ($this->peopleRepository->findGroupMembers('ROLE_LCM', $alvestBusinessUnit) as $legalComplianceManager) {
                $recipients[] = $legalComplianceManager;
            }

            // Add legal compliance managers from the ASM's region
            $asmRegion = $customer->getMainSalesRepresentative()->asm->getBusinessUnit()->getRegion();
            $recipients = array_merge($recipients, $this->peopleRepository->findGroupsMembersByRegion(['ROLE_LCM'], $asmRegion));
        }

        // Add the main sales representative (ASM)
        if (null !== $customer->getMainSalesRepresentative()) {
            $recipients[] = $customer->getMainSalesRepresentative()->asm;
        }

        return array_unique($recipients);
    }
}
