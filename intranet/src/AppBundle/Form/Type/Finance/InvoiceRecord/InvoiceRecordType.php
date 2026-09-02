<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Finance\InvoiceRecord;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Manager\Finance\InvoiceRecord\InvoiceRecordCategories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceRecordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', ChoiceType::class, [
                'label' => 'account_receivable.invoice_record.fields.category',
                'required' => true,
                'choice_translation_domain' => false,
                'choices' => InvoiceRecordCategories::getCategories(),
            ])
            ->add('expectedPaymentDate', DatePickerType::class, [
                'required' => true,
                'label' => 'account_receivable.invoice_record.fields.expected_payment_date',
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'fields.comment',
                'translation_domain' => 'messages',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'style' => 'resize:vertical',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'account_receivable',
        ]);
    }
}
