<?php

declare(strict_types=1);

namespace AppBundle\Subscription;

use ApiBundle\Client;
use ApiBundle\Hydra\HydraCollection;
use AppBundle\Controller\Mis\TroubleTicket\ShowController;
use AppBundle\Form\Type\Mis\TroubleTicket\TroubleTicketSettingType;

class TroubleTicketSubscription implements SubscriptionInterface
{
    public const string SUBSCRIPTION_NAME = 'tts.subscriptions';

    public function __construct(private readonly Client $client)
    {
    }

    public function supports(string $name): bool
    {
        return self::SUBSCRIPTION_NAME === $name;
    }

    public function getFormType(): string
    {
        return TroubleTicketSettingType::class;
    }

    public function getScalarFields(): array
    {
        return ['indiceFactor', 'status'];
    }

    public function getRoute(): string
    {
        return 'trouble_ticket_subscriptions';
    }

    public function getSubscribedItems(array $settings): HydraCollection
    {
        return $this->client->findBy(
            ShowController::RESOURCE_URL,
            $settings,
            ['createdAt' => 'DESC'],
        );
    }
}
