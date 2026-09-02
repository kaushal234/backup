<?php

declare(strict_types=1);

namespace App\Notifier\MinutesOfMeeting;

use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Repository\Common\SubscriptionRepository;

class RecipientsFinder
{
    private readonly SubscriptionRepository $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function findRecipients(Meeting $meeting, $recipients = []): array
    {
        foreach ($meeting->getActions() as $action) {
            $recipients[] = $action->getAssignee();
        }

        if (!$meeting->isConfidential()) {
            foreach ($meeting->getCustomers() as $customer) {
                if (null !== ($salesRepresentative = $customer->getMainSalesRepresentative())) {
                    $recipients[] = $salesRepresentative->asm;
                    if (null !== ($asmSupervisor = $salesRepresentative->asm->getSupervisor())) {
                        $recipients[] = $asmSupervisor;
                    }
                }

                foreach ($customer->getSecondarySalesRepresentatives() as $secondarySalesRepresentative) {
                    $recipients[] = $secondaryAsm = $secondarySalesRepresentative->asm;
                    if (null !== ($asmSupervisor = $secondaryAsm->getSupervisor())) {
                        $recipients[] = $asmSupervisor;
                    }
                }
            }
        }

        return [
            ...$recipients,
            ...$meeting->getAttendees()->toArray(),
            $meeting->getCreatedBy(),
        ];
    }

    public function findCc(Meeting $meeting)
    {
        $ccs = [];
        foreach ($this->subscriptionRepository->findByResource($meeting) as $subscription) {
            if (!$subscription->getUser() instanceof People) {
                continue;
            }
            $ccs[] = $subscription->getUser();
        }

        return $ccs;
    }
}
