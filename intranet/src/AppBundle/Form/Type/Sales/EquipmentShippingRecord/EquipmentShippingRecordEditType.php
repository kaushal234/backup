<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\EquipmentShippingRecord;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EquipmentShippingRecordEditType extends AbstractType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ([] !== $options['customer_choices']) {
            $builder->add('customer', ChoiceType::class, [
                'label' => 'demo.fields.customer',
                'translation_domain' => 'demo',
                'choices' => $options['customer_choices'],
            ]);
        }

        $builder->add('equipmentShippingRecordLines', CollectionType::class, [
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
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'equipment_shipping_record',
            'customer_choices' => [],
        ]);
    }

    public function getParent(): string
    {
        return EquipmentShippingRecordType::class;
    }
}
