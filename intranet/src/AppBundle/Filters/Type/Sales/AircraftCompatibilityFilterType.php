<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Sales\Catalogue\AircraftAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ManufacturerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductFamilyChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AircraftCompatibilityFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('products', ProductAutocompleteChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('productType', ProductTypeAutocompleteChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'property_path' => '[products.family.productType]',
            ])
            ->add('family', ProductFamilyChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'property_path' => '[products.family]',
            ])
            ->add('aircrafts', AircraftAutocompleteChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('manufacturer', ManufacturerAutocompleteChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'property_path' => '[aircrafts.manufacturer]',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary text-uppercase'],
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
