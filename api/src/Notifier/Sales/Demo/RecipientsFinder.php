<?php

declare(strict_types=1);

namespace App\Notifier\Sales\Demo;

use App\Entity\Directory\People;
use App\Entity\Sales\Demo;
use App\Repository\Common\SubscriptionRepository;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Module\ModuleRepository;

class RecipientsFinder
{
    private readonly PeopleRepository $peopleRepository;
    private readonly ModuleRepository $moduleRepository;
    private readonly SubscriptionRepository $subscriptionRepository;

    public function __construct(PeopleRepository $peopleRepository, ModuleRepository $moduleRepository, SubscriptionRepository $subscriptionRepository)
    {
        $this->peopleRepository = $peopleRepository;
        $this->moduleRepository = $moduleRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function findRecipients(Demo $demo): array
    {
        $ast = $demo->getAst();
        $csm = $ast->getSupervisor();

        $module = $this->moduleRepository->findByName('DEMO');
        $moo = $module->getOperationalOwner();

        /** @var array<People> $enabledApprovers */
        $enabledApprovers = array_filter($demo->getApprovers()->toArray(), static function ($approver) {
            return $approver->isEnabled();
        });

        $recipients = [
            ...[$demo->getAsm(), $demo->getPsm(), $ast, $csm, $moo],
            ...$enabledApprovers,
            ...$this->peopleRepository->findGroupsMembers(['ROLE_COO'], $demo->getFactory()),
            ...$this->peopleRepository->findGroupsMembers(['ROLE_SAM', 'ROLE_COO', 'ROLE_CSM', 'ROLE_SA'], $demo->getSso()),
            ...$this->peopleRepository->findGroupsMembers(['ROLE_CSD', 'ROLE_TCOO']),
        ];

        if (null !== $demo->getFactory()->getBusinessUnit()?->getRegion()) {
            $recipients = [
                ...$recipients,
                ...$this->peopleRepository->findGroupsMembersByRegion(['ROLE_RCEO', 'ROLE_RCOO'], $demo->getFactory()->getBusinessUnit()->getRegion()),
            ];
        }

        if (null !== $demo->getSso()->getBusinessUnit()?->getRegion()) {
            $recipients = [
                ...$recipients,
                ...$this->peopleRepository->findGroupsMembersByRegion(['ROLE_RCEO', 'ROLE_RCOO'], $demo->getSso()->getBusinessUnit()->getRegion()),
            ];
        }

        return array_unique($recipients);
    }

    public function findCc(Demo $demo)
    {
        $ccs = [];
        foreach ($this->subscriptionRepository->findByResource($demo) as $subscription) {
            if (!$subscription->getUser() instanceof People) {
                continue;
            }
            $ccs[] = $subscription->getUser();
        }

        return $ccs;
    }
}
