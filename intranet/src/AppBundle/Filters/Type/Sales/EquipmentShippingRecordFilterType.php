<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\FreightForwarder\FreightForwarderChoiceType;
use AppBundle\Form\Type\Sales\IncotermChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentShippingRecordFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', CustomerAutocompleteChoiceType::class, [
                'label' => 'demo.fields.customer',
                'translation_domain' => 'demo',
                'required' => false,
            ])
            ->add('sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('manufacturerLocation', FactoryChoiceType::class, [
                'label' => 'manufacturerLocation',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'property_path' => '[equipmentShippingRecordLines.equipmentRecord.manufacturerLocation]',
                'required' => false,
            ])
            ->add('status', SelectFormType::class, [
                'label' => 'demo.fields.status',
                'translation_domain' => 'demo',
                'required' => false,
                'multiple' => true,
                'choices' => [
                    'PENDING' => 'PENDING',
                    'BOOKED' => 'BOOKED',
                    'SHIPPED' => 'SHIPPED',
                    'CLOSED' => 'CLOSED',
                ],
            ])
            ->add('incoterm', IncotermChoiceType::class, [
                'label' => 'equipment_shipping_record.fields.incoterm',
                'required' => false,
            ])
            ->add('loadingPlace', TextType::class, [
                'label' => 'equipment_shipping_record.fields.loading',
                'required' => false,
            ])
            ->add('departurePlace', TextType::class, [
                'label' => 'equipment_shipping_record.fields.departure',
                'required' => false,
            ])
            ->add('arrivalPlace', TextType::class, [
                'label' => 'equipment_shipping_record.fields.arrival',
                'required' => false,
            ])
            ->add('forwarder', FreightForwarderChoiceType::class, [
                'label' => 'equipment_shipping_record.fields.forwarder',
                'required' => false,
            ])
            ->add('carrier', FreightForwarderChoiceType::class, [
                'label' => 'spq.quotations.fields.terms.forwarding_agent',
                'translation_domain' => 'spq',
                'required' => false,
            ])
            ->add('modality', ChoiceType::class, [
                'label' => 'equipment_shipping_record.fields.modality',
                'required' => false,
                'choices' => [
                    'AIR' => 'AIR',
                    'ROAD' => 'ROAD',
                    'SEA' => 'SEA',
                ],
            ])
            ->add('shipAuthorization', ChoiceType::class, [
                'label' => 'equipment_shipping_record.fields.ship_authorization',
                'required' => false,
                'choices' => [
                    '' => '',
                    'YES' => 1,
                    'NO' => 0,
                ],
            ])
            ->add('serialNumber', TextType::class, [
                'label' => 'tool.fields.serialNumber',
                'translation_domain' => 'tool',
                'property_path' => '[equipmentShippingRecordLines.equipmentRecord.serialNumber]',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.filter',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'btn btn-primary',
                    'style' => 'text-transform: uppercase;',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'equipment_shipping_record',
            'csrf_protection' => false,
            'method' => Request::METHOD_GET,
        ]);
    }
}
