<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Quality\Crab;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductFamilyChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CrabChartProductFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('factory', FactoryChoiceType::class, [
                'property_path' => '[equipmentRecord.manufacturerLocation]',
                'label' => 'fields.factory',
                'translation_domain' => 'messages',
            ])
            ->add('model', ProductAutocompleteChoiceType::class, [
                'property_path' => '[equipmentRecord.product]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('family', ProductFamilyChoiceType::class, [
                'property_path' => '[equipmentRecord.product.family]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('createdBefore', DatePickerType::class, [
                'label' => 'crab.fields.created_before',
                'translation_domain' => 'crab',
                'required' => true,
                'widget' => 'single_text',
            ])
            ->add('createdAfter', DatePickerType::class, [
                'label' => 'crab.fields.created_after',
                'translation_domain' => 'crab',
                'required' => true,
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
