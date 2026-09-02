<?php

declare(strict_types=1);

namespace App\Notifier\MIS\TroubleTicket;

use App\Entity\BaseTask;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\Type;
use App\Notifier\UserSettingSubscriptionResolver;
use App\Repository\Common\SubscriptionRepository;
use App\Repository\Directory\PeopleRepository;

class RecipientsFinder
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly PeopleRepository $peopleRepository,
        private readonly UserSettingSubscriptionResolver $subscriptionResolver,
    ) {
    }

    public function findTos(TroubleTicket $troubleTicket, bool $notifyOperationals = true): array
    {
        $recipients = [$troubleTicket->createdBy];

        if ($notifyOperationals) {
            $toAdd = array_filter([
                $troubleTicket->module->isNotifyOperationalOwner()
                    ? $troubleTicket->module->getOperationalOwner()
                    : null,
                $troubleTicket->assignee,
                $troubleTicket->misAssignee,
            ]);

            foreach ($toAdd as $recipient) {
                $recipients = [...$recipients, $recipient];
            }

            $localKeyUserExists = false;
            foreach ($troubleTicket->module->getLocalKeyUsers() as $localKeyUser) {
                if ($localKeyUser->getBusinessUnit()->getRegion() === $troubleTicket->createdBy->getBusinessUnit()->getRegion()) {
                    $localKeyUserExists = true;
                    $recipients = [...$recipients, $localKeyUser];
                }
            }
            if (!$localKeyUserExists && null !== $troubleTicket->module->getKeyUser() && $troubleTicket->module->isNotifyKeyUser()) {
                $recipients = [...$recipients, $troubleTicket->module->getKeyUser()];
            }
        }

        if (BaseTask::IF_1000 === $troubleTicket->indiceFactor) {
            $recipients = [
                ...$recipients,
                ...$this->peopleRepository->findGroupsMembers(['ROLE_CIO', 'ROLE_MISM']),
            ];

            if (Type::INCIDENT === $troubleTicket->type->type && str_contains($troubleTicket->type->description, 'security')) {
                $recipients = [
                    ...$recipients,
                    ...$this->peopleRepository->findGroupsMembers(['ROLE_GCFO', 'ROLE_GCEO']),
                ];
            }
        }

        return array_unique($recipients);
    }

    public function findCcs(TroubleTicket $troubleTicket): array
    {
        $ccs = [];
        foreach ($this->subscriptionRepository->findByResource($troubleTicket) as $subscription) {
            if (!$subscription->getUser() instanceof People) {
                continue;
            }

            $ccs[] = $subscription->getUser();
        }

        $ccs = [
            ...$ccs,
            ...$this->subscriptionResolver->findSubscribers('tts.subscriptions', $troubleTicket, ['status', 'indiceFactor']),
        ];

        return array_unique([
            ...$troubleTicket->getCcs(),
            ...$troubleTicket->getAdditionalOwners(),
            ...$ccs,
        ]);
    }
}
