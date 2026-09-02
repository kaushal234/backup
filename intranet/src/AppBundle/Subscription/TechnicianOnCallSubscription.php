<?php

declare(strict_types=1);

namespace AppBundle\Subscription;

use ApiBundle\Client;
use ApiBundle\Hydra\HydraCollection;
use AppBundle\DataPersister\Service\TechnicianOnCallPersister;
use AppBundle\Form\Type\Service\TechnicianOnCallSettingType;

class TechnicianOnCallSubscription implements SubscriptionInterface
{
    public const string SUBSCRIPTION_NAME = 'toc.subscriptions';

    public function __construct(private readonly Client $client)
    {
    }

    public function supports(string $name): bool
    {
        return self::SUBSCRIPTION_NAME === $name;
    }

    public function getFormType(): string
    {
        return TechnicianOnCallSettingType::class;
    }

    public function getScalarFields(): array
    {
        return ['indiceFactor'];
    }

    public function getRoute(): string
    {
        return 'technician_on_calls_subscriptions';
    }

    public function getSubscribedItems(array $settings): HydraCollection
    {
        return $this->client->findBy(
            TechnicianOnCallPersister::RESOURCE_URL,
            [...$settings, ...['normalizationGroups' => ['equipment_record']]],
            ['createdAt' => 'DESC'],
        );
    }
}
