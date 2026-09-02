<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\EquipmentShippingRecord;

use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\FreightForwarder\FreightForwarderChoiceType;
use AppBundle\Form\Type\Sales\IncotermChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EquipmentShippingRecordType extends AbstractType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sso', SSOChoiceType::class, [
                'label' => 'directory.department.fields.sso',
                'translation_domain' => 'directory',
                'required' => true,
            ])
            ->add('modality', ChoiceType::class, [
                'label' => 'equipment_shipping_record.fields.modality',
                'choices' => [
                    'AIR' => 'AIR',
                    'ROAD' => 'ROAD',
                    'SEA' => 'SEA',
                ],
            ])
            ->add('incoterm', IncotermChoiceType::class, [
                'label' => 'equipment_shipping_record.fields.incoterm',
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
            ->add('notes', TextareaType::class, [
                'label' => 'equipment_shipping_record.fields.notes',
                'required' => false,
                'attr' => [
                    'style' => 'resize:vertical; height:200px',
                ],
            ])
            ->add('shipAuthorization', CheckboxType::class, [
                'label' => 'equipment_shipping_record.fields.ship_authorization_granted',
                'required' => false,
            ])
            ->add('customer', CustomerAutocompleteChoiceType::class, [
                'label' => 'demo.fields.customer',
                'translation_domain' => 'demo',
            ])
            ->add('equipmentShippingRecordLines', CollectionType::class, [
                'entry_type' => EquipmentShippingRecordLineType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
                'required' => false,
                'entry_options' => [
                    'translation_domain' => 'equipment_shipping_record',
                    'can_edit_pickup_confirmation' => $this->authorizationChecker->isGranted('FEATURE_EQUIPMENT_SHIPPING_RECORD_LINE_PICK_UP_CONFIRMATION')
                        || $this->authorizationChecker->isGranted('MOO_ESR'),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'btn btn-info',
                    'style' => 'text-transform: uppercase;',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'equipment_shipping_record',
        ]);
    }
}
