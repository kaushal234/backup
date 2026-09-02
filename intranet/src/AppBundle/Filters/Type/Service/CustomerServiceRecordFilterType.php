<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Service;

use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\Directory\Location\LocationAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerServiceRecordFilterType extends AbstractType
{
    public const CUSTOMER_SERVICE_RECORD_TYPE = [
        'Default' => 'default',
        'TOC' => 'toc',
        'Service Bulletin' => 'sb',
        'Commissioning' => 'commissioning',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createdBy', PeopleAutocompleteChoiceType::class, [
                'label' => 'fields.created_by',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('leader', PeopleAutocompleteChoiceType::class, [
                'property_path' => '[interventions.leader]',
                'label' => 'intervention.fields.leader',
                'translation_domain' => 'customer_service_record',
                'required' => false,
            ])
            ->add('salesOrganisation', SSOChoiceType::class, [
                'label' => 'er.fields.sso',
                'translation_domain' => 'customer_service_record',
                'property_path' => '[equipmentRecord.salesOrganisation]',
                'required' => false,
            ])
            ->add('salesOrganisationService', SSOChoiceType::class, [
                'label' => 'er.fields.ssoService',
                'translation_domain' => 'customer_service_record',
                'property_path' => '[equipmentRecord.salesOrganisationService]',
                'required' => false,
            ])
            ->add('location', LocationAutocompleteChoiceType::class, [
                'property_path' => '[equipmentRecord.manufacturerLocation]',
                'label' => 'er.fields.location',
                'translation_domain' => 'customer_service_record',
                'required' => false,
            ])
            ->add('endUser', CustomerAutocompleteChoiceType::class, [
                'property_path' => '[equipmentRecord.endUser]',
                'label' => 'csr.fields.end_user',
                'translation_domain' => 'customer_service_record',
                'required' => false,
            ])
            ->add('equipmentRecord', EquipmentRecordAutocompleteChoiceType::class, [
                'label' => 'crab.fields.equipment_record',
                'translation_domain' => 'crab',
                'required' => false,
            ])
            ->add('model', ProductAutocompleteChoiceType::class, [
                'property_path' => '[equipmentRecord.product]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('productType', ProductTypeAutocompleteChoiceType::class, [
                'property_path' => '[equipmentRecord.product.family.productType]',
                'required' => false,
                'multiple' => true,
                'label' => 'catalogue.family.type',
                'translation_domain' => 'catalogue',
            ])
            ->add('airport', AirportChoiceType::class, [
                'required' => false,
                'label' => 'csr.fields.by_airport',
            ])
            ->add('status', SelectFormType::class, [
                'label' => 'customers.fields.status',
                'required' => false,
                'multiple' => true,
                'choices' => [
                    'PENDING' => 'PENDING',
                    'PLANNED' => 'PLANNED',
                    'ASSIGNED' => 'ASSIGNED',
                    'IN-PROGRESS' => 'IN-PROGRESS',
                    'COMPLETED' => 'COMPLETED',
                    'CLOSED' => 'CLOSED',
                ],
                'translation_domain' => 'sales_customers',
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
            ->add('completedAfter', DatePickerType::class, [
                'label' => 'csr.fields.completed_at_after',
                'property_path' => '[completedAt][after]',
                'required' => false,
            ])
            ->add('completedBefore', DatePickerType::class, [
                'label' => 'csr.fields.completed_at_before',
                'property_path' => '[completedAt][before]',
                'required' => false,
            ])
            ->add('closedAfter', DatePickerType::class, [
                'label' => 'wms.form.closed_at.after',
                'property_path' => '[closedAt][after]',
                'translation_domain' => 'wms',
                'required' => false,
            ])
            ->add('closedBefore', DatePickerType::class, [
                'label' => 'wms.form.closed_at.before',
                'property_path' => '[closedAt][before]',
                'translation_domain' => 'wms',
                'required' => false,
            ])
            ->add('discriminator', SelectFormType::class, [
                'required' => false,
                'multiple' => true,
                'label' => 'csr.fields.csr_type',
                'choices' => self::CUSTOMER_SERVICE_RECORD_TYPE,
            ])
            ->add('download', SubmitType::class, [
                'label' => 'menu.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-uppercase'],
            ])
            ->add('country', CountryChoiceType::class, [
                'label' => 'demo.fields.country',
                'translation_domain' => 'demo',
                'property_path' => '[airport.country]',
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'customer_service_record',
        ]);
    }
}
