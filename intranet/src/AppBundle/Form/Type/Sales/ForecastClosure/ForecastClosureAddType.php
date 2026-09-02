<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ForecastClosure;

use AppBundle\Form\Type\BasicFileType;
use AppBundle\Form\Type\Finance\CurrencyChoiceType;
use AppBundle\Form\Type\Sales\Competitor\CompetitorChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForecastClosureAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'sales_forecasts.fields.status',
                'translation_domain' => 'sales_forecasts',
                'required' => true,
                'choices' => $options['status'],
            ])
            ->add('reason', ChoiceType::class, [
                'label' => 'forecast_closures.fields.reason',
                'translation_domain' => 'forecast_closures',
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
            ->add('price', NumberType::class, [
                'label' => 'forecast_closures.fields.price',
                'translation_domain' => 'forecast_closures',
                'data' => $options['sales_forecast']['price'] ?? null,
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'label' => 'competitor_pricings.fields.currency',
                'translation_domain' => 'competitor_pricings',
                'placeholder' => '',
                'data' => $options['sales_forecast']['sso']['currency']['@id'] ?? null,
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'fields.comment',
                'translation_domain' => 'messages',
                'required' => true,
                'attr' => [
                    'style' => 'resize:vertical',
                ],
            ])
            ->add('file', BasicFileType::class, [
                'label' => 'forecast_closures.files',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA,
            static function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $event->getData();

                $quantityParameters = [
                    'label' => 'forecast_closures.fields.quantity',
                    'required' => true,
                    'translation_domain' => 'forecast_closures',
                    'data' => $options['sales_forecast']['quantity'],
                ];

                $competitorParameters = [
                    'label' => 'competitors.name',
                    'required' => false,
                    'translation_domain' => 'sales_competitors',
                ];

                if (isset($data['status'])) {
                    if (str_contains($data['status'], 'PARTIAL')) {
                        $quantityParameters['data'] = null;
                    }
                    switch ($data['status']) {
                        case 'PARTIAL-ORDERED':
                        case 'ORDERED':
                            $quantityParameters['label'] = 'forecast_closures.fields.ordered_quantity';
                            $competitorParameters['label'] = 'forecast_closures.fields.winning_party';
                            if (null !== $options['sales_forecast'] && null !== ($network = $options['sales_forecast']['sso']['network'])) {
                                $competitorParameters['choices'] = [$network['name'] => ''];
                                $competitorParameters['disabled'] = true;
                            }
                            $competitorParameters['translation_domain'] = 'forecast_closures';
                            break;
                        case 'PARTIAL-LOST':
                        case 'LOST':
                            $quantityParameters['label'] = 'forecast_closures.fields.lost_quantity';
                            break;
                    }
                }

                $form->add('orderedQuantity', IntegerType::class, $quantityParameters);
                $form->add('competitor', CompetitorChoiceType::class, $competitorParameters);
            })
        ;

        $builder->get('status')->setDisabled(true);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'forecast_closures',
            'allow_extra_fields' => true,
            'status' => [],
            'sales_forecast' => null,
            'csrf_protection' => false,
        ]);
    }
}
