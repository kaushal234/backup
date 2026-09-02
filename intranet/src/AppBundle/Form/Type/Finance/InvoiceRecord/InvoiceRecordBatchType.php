<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Finance\InvoiceRecord;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class InvoiceRecordBatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accountReceivables', CollectionType::class, [
                'entry_type' => InvoiceRecordQuickAdminType::class,
            ])
            ->add('comment', TextareaType::class, [
                'label' => false,
                'required' => true,
                'empty_data' => null,
                'attr' => [
                    'style' => 'resize:vertical',
                    'placeholder' => 'Type your comment here...',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }
}
