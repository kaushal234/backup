<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use AppBundle\Manager\SettingsManager;
use AppBundle\Registry\SubscriptionRegistry;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent]
class SubscriptionComponent
{
    public string $key;

    public function __construct(
        private readonly SubscriptionRegistry $registry,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function mount(string $key): void
    {
        $this->key = $key;
        $this->registry->get($key);
    }

    #[ExposeInTemplate('items')]
    public function getItems(): iterable
    {
        $settings = $this->settingsManager->get($this->key) ?? [];
        $subscription = $this->registry->get($this->key);

        return 0 === \count($settings) ? [] : $subscription->getSubscribedItems($settings);
    }
}
