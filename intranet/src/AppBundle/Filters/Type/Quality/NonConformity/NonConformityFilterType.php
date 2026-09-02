<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Quality\NonConformity;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\LocationAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\NonConformity\ProcessChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NonConformityFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', LocationAutocompleteChoiceType::class, [
                'label' => 'fields.location',
                'required' => $options['locationRequired'],
                'translation_domain' => 'messages',
                'multiple' => (bool) $options['enable_location_attributes'],
            ])
            ->add('model', ProductAutocompleteChoiceType::class, [
                'property_path' => '[products]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('type', ProductTypeAutocompleteChoiceType::class, [
                'property_path' => '[products.family.productType]',
                'label' => 'crab.fields.type',
                'multiple' => true,
                'required' => false,
                'translation_domain' => 'crab',
            ])
            ->add('processes', ProcessChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('createdAfter', DatePickerType::class, [
                'label' => 'sales_forecasts.fields.created_after',
                'property_path' => '[createdAt][after]',
                'translation_domain' => 'sales_forecasts',
                'required' => false,
            ])
            ->add('createdBefore', DatePickerType::class, [
                'label' => 'spq.form.created_at_before',
                'property_path' => '[createdAt][before]',
                'translation_domain' => 'spq',
                'required' => false,
            ])
            ->add('limit', ChoiceType::class, [
                'label' => 'non_conformity.fields.limit',
                'translation_domain' => 'non_conformity',
                'required' => false,
                'choices' => [
                    'Top 10' => 10,
                    'Top 20' => 20,
                    'Top 30' => 30,
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'locationRequired' => false,
            'enable_location_attributes' => false,
        ]);
    }
}
