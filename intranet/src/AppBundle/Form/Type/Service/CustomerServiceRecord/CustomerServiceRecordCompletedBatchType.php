<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerServiceRecordCompletedBatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('completedCustomerServiceRecords', CollectionType::class, [
                'entry_type' => CustomerServiceRecordCheckCompletedType::class,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }
}
