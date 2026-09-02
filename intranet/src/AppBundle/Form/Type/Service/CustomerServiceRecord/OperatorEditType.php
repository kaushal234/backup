<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class OperatorEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('operators', CollectionType::class, [
                'label' => 'csr.fields.operators',
                'translation_domain' => 'customer_service_record',
                'entry_type' => TechniciansChoiceType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-info']])
        ;
    }
}
