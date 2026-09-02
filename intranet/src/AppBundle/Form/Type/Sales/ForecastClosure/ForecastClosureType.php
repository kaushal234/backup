<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ForecastClosure;

use AppBundle\Form\Type\Finance\CurrencyChoiceType;
use AppBundle\Form\Type\Sales\Competitor\CompetitorChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ForecastClosureType extends AbstractType
{
    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $moo = $this->authorizationChecker->isGranted('MOO_SFR');

        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'forecast_closures.fields.status',
                'required' => true,
                'disabled' => !$moo,
                'attr' => [
                    'readonly' => !$moo,
                ],
                'choices' => [
                    'ORDERED' => 'ORDERED',
                    'LOST' => 'LOST',
                    'PARTIAL-ORDERED' => 'PARTIAL-ORDERED',
                    'PARTIAL-LOST' => 'PARTIAL-LOST',
                ],
            ])
            ->add('orderedQuantity', IntegerType::class, [
                'label' => 'forecast_closures.fields.quantity',
                'translation_domain' => 'forecast_closures',
            ])
            ->add('reason', ChoiceType::class, [
                'label' => 'forecast_closures.fields.reason',
                'required' => true,
                'choices' => [
                    '' => '',
                    'Technical / Equipment Performance' => 'PERFORMANCE',
                    'Service & Spare Part Support' => 'SUPPORT',
                    'Sales Job' => 'SALES',
                    'Requirement Cancelled' => 'CANCELLED',
                    'Price' => 'PRICE',
                    'Payment Terms' => 'PAYMENT',
                    'Lead-Time' => 'LEAD_TIME',
                    'Customer Loyalty' => 'LOYALTY',
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'fields.comment',
                'translation_domain' => 'messages',
            ])
            ->add('price', NumberType::class, [
                'label' => 'forecast_closures.fields.price',
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'label' => 'competitor_pricings.fields.currency',
                'translation_domain' => 'competitor_pricings',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA,
            static function (FormEvent $event) use ($options, $moo) {
                $form = $event->getForm();
                $data = $event->getData();

                $competitorParameters = [
                    'label' => 'competitors.name',
                    'translation_domain' => 'sales_competitors',
                    'placeholder' => '',
                    'required' => false,
                ];

                $network = $options['sales_forecast']['sso']['network'];

                if (
                    !$moo
                    && null !== $network
                    && null !== $options['sales_forecast']
                    && \in_array($data['status'], ['ORDERED', 'PARTIAL-ORDERED'], true)
                ) {
                    $competitorParameters['choices'] = [$network['name'] => ''];
                    $competitorParameters['disabled'] = true;
                } elseif ($moo && null !== $network) {
                    $competitorParameters['extra_choices'] = \in_array($data['status'], ['ORDERED', 'PARTIAL-ORDERED'], true) ? [$network['name'] => ''] : ['' => ''];
                    $competitorParameters['required'] = false;
                }

                $form
                    ->add('competitor', CompetitorChoiceType::class, $competitorParameters)
                ;
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'forecast_closures',
            'submit_label' => 'button.submit',
            'submit_translation_domain' => 'messages',
            'allow_extra_fields' => true,
            'sales_forecast' => null,
        ]);
    }
}
