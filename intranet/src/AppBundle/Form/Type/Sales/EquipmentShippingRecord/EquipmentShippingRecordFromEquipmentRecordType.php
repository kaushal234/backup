<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\EquipmentShippingRecord;

use ApiBundle\Model\ApiData;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentShippingRecordFromEquipmentRecordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ApiData $equipmentRecord */
        $equipmentRecord = $options['equipment_record'];

        $builder->add('sso', SSOChoiceType::class, [
            'label' => 'directory.department.fields.sso',
            'translation_domain' => 'directory',
            'query' => [
                'order' => [
                    'name' => 'ASC',
                ],
                'capability.sso' => true,
                'name' => $equipmentRecord['salesOrganisation']['name'],
            ],
            'required' => true,
        ]);

        $choices = [];
        $endUser = $equipmentRecord['endUser'] ?? null;
        $buyer = $equipmentRecord['buyer'] ?? null;
        if (null !== $endUser) {
            $choices[$endUser['name']] = $endUser['@id'];
        }
        if (null !== $buyer && $buyer['@id'] !== ($endUser['@id'] ?? null)) {
            $choices[$buyer['name']] = $buyer['@id'];
        }

        $builder->add('customer', ChoiceType::class, [
            'label' => 'demo.fields.customer',
            'translation_domain' => 'demo',
            'choices' => $choices,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($equipmentRecord): void {
            $data = $event->getData() ?? [];

            if (empty($data['equipmentShippingRecordLines'])) {
                $data['equipmentShippingRecordLines'] = [
                    ['equipmentRecord' => $equipmentRecord['@id']],
                ];
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['equipment_record'])
            ->setAllowedTypes('equipment_record', [ApiData::class]);
        $resolver->setDefaults([
            'translation_domain' => 'equipment_shipping_record',
        ]);
    }

    public function getParent(): string
    {
        return EquipmentShippingRecordType::class;
    }
}
