<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Parts;

use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DropShipmentFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderDateAfter', DatePickerType::class, [
                'label' => 'parts.kpi.drop_shipment.order_date_gte',
                'property_path' => '[orderDate][after]',
                'required' => true,
                'empty_data' => (new \DateTime('first day of last month'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
                'attr' => [
                    'max' => date('Y-m-d'),
                ],
            ])
            ->add('orderDateBefore', DatePickerType::class, [
                'label' => 'parts.kpi.drop_shipment.order_date_lte',
                'property_path' => '[orderDate][before]',
                'required' => true,
                'empty_data' => (new \DateTime('last day of last month'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
                'attr' => [
                    'max' => date(DatePickerType::DEFAULT_INPUT_FORMAT),
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'parts',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }
}
