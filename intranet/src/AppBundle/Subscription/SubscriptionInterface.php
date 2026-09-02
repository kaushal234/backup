<?php

declare(strict_types=1);

namespace AppBundle\Subscription;

use ApiBundle\Hydra\HydraCollection;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.subscription')]
interface SubscriptionInterface
{
    public function supports(string $name): bool;

    public function getFormType(): string;

    public function getScalarFields(): array;

    public function getRoute(): string;

    public function getSubscribedItems(array $settings): HydraCollection;
}
