<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\EquipmentShippingRecord;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentShippingRecordFromSolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('customer', ChoiceType::class, [
            'label' => 'demo.fields.customer',
            'translation_domain' => 'demo',
            'choices' => $options['customer_choices'],
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($options): void {
            $data = $event->getData() ?? [];

            if (!isset($data['sso']) && null !== $options['sso']) {
                $data['sso'] = $options['sso'];
            }

            if (!isset($data['incoterm']) && null !== $options['incoterm']) {
                $data['incoterm'] = $options['incoterm'];
            }

            if (!isset($data['shipAuthorization'])) {
                $data['shipAuthorization'] = $options['ship_authorization'];
            }

            if (empty($data['equipmentShippingRecordLines'])) {
                $data['equipmentShippingRecordLines'] = array_map(
                    static fn (string $equipmentRecordId) => ['equipmentRecord' => $equipmentRecordId],
                    $options['equipment_record_ids'],
                );
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired([
                'customer_choices',
                'sso',
                'equipment_record_ids',
            ])
            ->setDefaults([
                'translation_domain' => 'equipment_shipping_record',
                'incoterm' => null,
                'ship_authorization' => false,
            ])
            ->setAllowedTypes('customer_choices', ['array'])
            ->setAllowedTypes('sso', ['string'])
            ->setAllowedTypes('equipment_record_ids', ['array'])
            ->setAllowedTypes('incoterm', ['null', 'string'])
            ->setAllowedTypes('ship_authorization', ['bool']);
    }

    public function getParent(): string
    {
        return EquipmentShippingRecordType::class;
    }
}
