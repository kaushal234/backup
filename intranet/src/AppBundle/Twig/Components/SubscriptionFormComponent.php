<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use AppBundle\Registry\SubscriptionRegistry;
use AppBundle\Subscription\SubscriptionFormFactory;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent]
class SubscriptionFormComponent
{
    public string $key;

    public function __construct(
        private readonly SubscriptionFormFactory $formFactory,
        private readonly SubscriptionRegistry $registry,
    ) {
    }

    public function mount(string $key): void
    {
        $this->key = $key;
        $this->registry->get($key);
    }

    #[ExposeInTemplate('form')]
    public function getForm(): FormView
    {
        $form = $this->formFactory->create($this->key);

        return $form->createView();
    }
}
