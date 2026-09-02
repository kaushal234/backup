<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Catalogue;

use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Finance\FinanceFamilyChoiceType;
use AppBundle\Form\Type\Manufacturing\ManufacturingFamilyChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'catalogue.products.name',
            ])
            ->add('family', ProductFamilyChoiceType::class, [
                'label' => 'catalogue.family.name',
                'data' => $options['family'],
            ])
            ->add('erpLocation', FactoryChoiceType::class, [
                'label' => 'catalogue.products.erp',
                'translation_domain' => 'catalogue',
            ])
            ->add('financeFamily', FinanceFamilyChoiceType::class, [
                'label' => 'catalogue.products.finance_family',
                'placeholder' => '',
            ])
            ->add('manufacturingFamily', ManufacturingFamilyChoiceType::class, [
                'label' => 'catalogue.products.manufacturing_family',
                'placeholder' => '',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'catalogue.family.description',
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
            ])
            ->add('productStandardItems', CollectionType::class, [
                'entry_type' => ProductStandardItem::class,
                'entry_options' => [
                    'label' => false,
                ],
                'label' => 'catalogue.products.standard_item',
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('innovativeLevel', ChoiceType::class, [
                'choices' => [
                    'Introduction' => 'Introduction',
                    'Major Redesign' => 'Major Redesign',
                ],
                'label' => 'catalogue.products.innovative_level',
                'required' => false,
            ])
            ->add('hidden', CheckboxType::class, [
                'required' => false,
                'label' => 'catalogue.products.hidden',
            ])
            ->add('light', CheckboxType::class, [
                'required' => false,
                'label' => 'catalogue.products.light',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'catalogue.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        if (!$options['add']) {
            foreach (array_keys($builder->all()) as $key) {
                if (!\in_array($key, $options['authorizedFields'], true) && 'submit' !== $key) {
                    $field = $builder->get($key);
                    $field->setDisabled(true);
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'catalogue',
            'family' => null,
            'product_type' => null,
            'authorizedFields' => [],
            'add' => false,
        ]);
    }
}
