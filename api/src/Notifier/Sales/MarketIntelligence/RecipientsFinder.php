<?php

declare(strict_types=1);

namespace App\Notifier\Sales\MarketIntelligence;

use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Sales\MarketIntelligenceSubscriptionRepository;

class RecipientsFinder
{
    private readonly PeopleRepository $peopleRepository;
    private readonly MarketIntelligenceSubscriptionRepository $marketIntelligenceSubscriptionRepository;

    public function __construct(PeopleRepository $peopleRepository, MarketIntelligenceSubscriptionRepository $marketIntelligenceSubscriptionRepository)
    {
        $this->peopleRepository = $peopleRepository;
        $this->marketIntelligenceSubscriptionRepository = $marketIntelligenceSubscriptionRepository;
    }

    public function findRecipients(MarketIntelligence $marketIntelligence)
    {
        $recipients = [
            ...$this->marketIntelligenceSubscriptionRepository->findSubscriberForMarketIntelligence($marketIntelligence),
            ...$this->peopleRepository->findNewPeopleWithNoSubscriptions($marketIntelligence),
        ];

        $recipients[] = $marketIntelligence->getPoster();

        return array_unique($recipients);
    }
}
