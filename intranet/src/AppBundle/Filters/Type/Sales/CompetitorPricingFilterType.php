<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Competitor\CompetitorAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitorPricingFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('competitor', CompetitorAutocompleteChoiceType::class, [
                'label' => 'forecast_closures.fields.winning_party',
                'required' => false,
            ])
            ->add('product', ProductAutocompleteChoiceType::class, [
                'required' => false,
                'label' => 'catalogue.family.products',
                'translation_domain' => 'catalogue',
                'property_path' => '[forecastClosure.salesForecast.product]',
            ])
            ->add('status', SelectFormType::class, [
                'label' => 'forecast_closures.fields.fcr_status',
                'required' => false,
                'choices' => [
                    '' => '',
                    'PARTIAL-ORDERED' => 'PARTIAL-ORDERED',
                    'PARTIAL-LOST' => 'PARTIAL-LOST',
                    'PARTIAL-*' => 'PARTIAL',
                    'ORDERED' => 'ORDERED',
                    'LOST' => 'LOST',
                ],
                'property_path' => '[forecastClosure.status]',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'forecast_closures',
            'csrf_protection' => false,
        ]);
    }
}
