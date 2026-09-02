<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Support;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Support\EmissionRatingChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentRecordFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('salesOrganisation', SSOChoiceType::class, [
                'label' => 'support.equipment_record.fields.sales_organisation',
                'translation_domain' => 'support',
                'required' => false,
            ])
            ->add('manufacturerLocation', FactoryChoiceType::class, [
                'label' => 'support.equipment_record.fields.manufacturer_location',
                'translation_domain' => 'support',
                'required' => false,
            ])
            ->add('product', ProductAutocompleteChoiceType::class, [
                'label' => 'catalogue.products.product',
                'required' => false,
                'translation_domain' => 'catalogue',
            ])
            ->add('productType', ProductTypeAutocompleteChoiceType::class, [
                'label' => 'catalogue.type.product_type',
                'property_path' => '[product.family.productType]',
                'required' => false,
                'translation_domain' => 'catalogue',
            ])
            ->add('emissionRating', EmissionRatingChoiceType::class, [
                'label' => 'sales_forecasts.fields.tier',
                'required' => false,
                'translation_domain' => 'sales_forecasts',
            ])
            ->add('buyer', CustomerAutocompleteChoiceType::class, [
                'label' => 'support.equipment_record.fields.buyer',
                'required' => false,
                'multiple' => true,
            ])
            ->add('asm', ASMAutocompleteChoiceType::class, [
                'property_path' => '[buyer.mainSalesRepresentative.asm]',
                'label' => 'customers.fields.contact_and_asm',
                'translation_domain' => 'sales_customers',
                'required' => false,
            ])
            ->add('endUser', CustomerAutocompleteChoiceType::class, [
                'label' => 'support.equipment_record.fields.end_user',
                'required' => false,
                'multiple' => true,
            ])
            ->add('combinationMode', ChoiceType::class, [
                'label' => 'support.equipment_record.fields.combination_mode',
                'required' => false,
                'choices' => [
                    'ER COMBINED' => 'ER COMBINED',
                    'PRE-ASSEMBLY' => 'PRE-ASSEMBLY',
                ],
            ])
            ->add('light', CheckboxType::class, [
                'label' => 'support.equipment_record.fields.light',
                'required' => false,
            ])
            ->add('excludeLight', CheckboxType::class, [
                'label' => 'support.equipment_record.fields.exclude_light',
                'required' => false,
            ])
            ->add('isShipped', CheckboxType::class, [
                'label' => 'support.equipment_record.fields.shipped',
                'required' => false,
            ])
            ->add('serialNumber', TextType::class, [
                'label' => 'tool.fields.serialNumber',
                'translation_domain' => 'tool',
                'required' => false,
            ])
            ->add('greenTagDateBefore', DatePickerType::class, [
                'label' => 'support.equipment_record.fields.green_tag_date_before',
                'property_path' => '[greenTagDate][before]',
                'required' => false,
            ])
            ->add('greenTagDateAfter', DatePickerType::class, [
                'label' => 'support.equipment_record.fields.green_tag_date_after',
                'property_path' => '[greenTagDate][after]',
                'required' => false,
            ])
            ->add('firstGreenTagDateBefore', DatePickerType::class, [
                'label' => 'support.equipment_record.fields.first_green_tag_date_before',
                'property_path' => '[firstGreenTagDate][before]',
                'required' => false,
            ])
            ->add('firstGreenTagDateAfter', DatePickerType::class, [
                'label' => 'support.equipment_record.fields.first_green_tag_date_after',
                'property_path' => '[firstGreenTagDate][after]',
                'required' => false,
            ])
            ->add('estimatedGreenTagDateBefore', DatePickerType::class, [
                'label' => 'support.equipment_record.fields.estimated_green_tag_date_after',
                'property_path' => '[estimatedGreenTagDate][after]',
                'required' => false,
            ])
            ->add('estimatedGreenTagDateAfter', DatePickerType::class, [
                'label' => 'support.equipment_record.fields.estimated_green_tag_date_before',
                'property_path' => '[estimatedGreenTagDate][before]',
                'required' => false,
            ])
            ->add('orderLineNumber', TextType::class, [
                'label' => 'customers.menu.sol',
                'translation_domain' => 'sales_customers',
                'property_path' => '[orderFactory.orderLine.legacyId]',
                'required' => false,
            ])
            ->add('inspection', CheckboxType::class, [
                'label' => 'support.equipment_record.fields.inspection',
                'property_path' => '[orderFactory.orderLine.inspection]',
                'required' => false,
            ])
            ->add('model', TextType::class, [
                'required' => false,
                'property_path' => '[model]',
            ])
            ->add('downloadCsv', SubmitType::class, [
                'label' => 'menu.download_csv',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-uppercase'],
            ])
            ->add('downloadXls', SubmitType::class, [
                'label' => 'menu.download_xls',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-uppercase'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'support',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'method' => Request::METHOD_GET,
        ]);
    }
}
