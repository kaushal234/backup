<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\People\PeopleChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NestedCustomerServiceRecordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('leader', PeopleChoiceType::class, [
                'label' => 'csr.fields.technician',
                'filters' => [
                    'department' => ['/departments/1', '/departments/2'],
                    'normalization_groups_override' => ['people_list'],
                ],
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'attr' => ['class' => 'chosen-select'],
            ])
            ->add('plannedAt', DatePickerType::class, [
                'defaultDate' => new \DateTime(),
                'label' => 'csr.fields.planned_date',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'customer_service_record',
        ]);
    }
}
