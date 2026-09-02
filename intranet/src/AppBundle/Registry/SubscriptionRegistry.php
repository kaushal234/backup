<?php

declare(strict_types=1);

namespace AppBundle\Registry;

use AppBundle\Subscription\SubscriptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class SubscriptionRegistry
{
    /** @var iterable<SubscriptionInterface> */
    private iterable $subscriptions = [];

    public function __construct(#[AutowireIterator('app.subscription')] iterable $subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    public function get(string $name): SubscriptionInterface
    {
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->supports($name)) {
                return $subscription;
            }
        }

        throw new \InvalidArgumentException(\sprintf('Subscription "%s" does not exist.', $name));
    }
}
