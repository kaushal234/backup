<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeChoiceType;
use AppBundle\Form\Type\Support\EmissionRatingChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductCertificateFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productType', ProductTypeChoiceType::class, [
                'label' => 'catalogue.type.product_type',
                'required' => false,
            ])
            ->add('product', ProductAutocompleteChoiceType::class, [
                'label' => 'catalogue.products.product',
                'translation_domain' => 'catalogue',
                'required' => false,
            ])
            ->add('emissionRatings', EmissionRatingChoiceType::class, [
                'label' => 'sales_forecasts.fields.tier',
                'translation_domain' => 'sales_forecasts',
                'required' => false,
                'multiple' => true,
            ])
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'directory.department.fields.factory',
                'translation_domain' => 'directory',
                'required' => false,
            ])
            ->add('expiredAt-after', DatePickerType::class, [
                'label' => 'catalogue.certificates.fields.expired_at_after',
                'property_path' => '[expiredAt][after]',
                'required' => false,
            ])
            ->add('expiredAt-before', DatePickerType::class, [
                'label' => 'catalogue.certificates.fields.expired_at_before',
                'property_path' => '[expiredAt][before]',
                'required' => false,
            ])
            ->add('expectedAt-after', DatePickerType::class, [
                'label' => 'catalogue.certificates.fields.expected_at_after',
                'property_path' => '[expectedAt][after]',
                'required' => false,
            ])
            ->add('expectedAt-before', DatePickerType::class, [
                'label' => 'catalogue.certificates.fields.expected_at_before',
                'property_path' => '[expectedAt][before]',
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'catalogue',
            'csrf_protection' => false,
        ]);
    }
}
