<?php

declare(strict_types=1);

namespace App\Event\Activity;

use App\Entity\Common\Subscription;
use Symfony\Contracts\EventDispatcher\Event;

class SubscriptionCreatedEvent extends Event
{
    private readonly Subscription $subscription;
    private readonly object $item;

    public function __construct(Subscription $subscription, object $item)
    {
        $this->subscription = $subscription;
        $this->item = $item;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    public function getItem(): object
    {
        return $this->item;
    }
}
