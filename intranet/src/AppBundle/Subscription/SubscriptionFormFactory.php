<?php

declare(strict_types=1);

namespace AppBundle\Subscription;

use AppBundle\Manager\SettingsManager;
use AppBundle\Registry\SubscriptionRegistry;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;

class SubscriptionFormFactory
{
    public function __construct(
        private readonly SubscriptionRegistry $registry,
        private readonly SettingsManager $settingsManager,
        private readonly FormFactoryInterface $formFactory,
        private readonly RouterInterface $router,
    ) {
    }

    public function create(string $key): FormInterface
    {
        $moduleSubscription = $this->registry->get($key);
        $settings = $this->settingsManager->get($key) ?? [];
        $scalarFields = $moduleSubscription->getScalarFields();

        $formBuilder = $this->formFactory->createBuilder($moduleSubscription->getFormType(), $settings, [
            'action' => $this->router->generate('subscriptions', ['settingKey' => $key]),
        ]);

        $formBuilder
            ->add('submit', SubmitType::class, ['label' => 'button.save'])
            ->add('delete', SubmitType::class, ['label' => 'button.delete'])
        ;

        $formBuilder->addModelTransformer(new CallbackTransformer(
            static function ($settings) use ($scalarFields) {
                $parameters = [];
                foreach ($settings as $field => $values) {
                    foreach ($values as $value) {
                        $parameters[$field] = \in_array($field, $scalarFields, true)
                            ? [...$parameters[$field] ?? [], $value]
                            : [...$parameters[$field] ?? [], ['@id' => $value]];
                    }
                }

                return $parameters;
            },
            static function ($settings) {
                return $settings;
            }
        ));

        return $formBuilder->getForm();
    }
}
