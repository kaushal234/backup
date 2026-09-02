<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Finance\InvoiceRecord;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Manager\Finance\InvoiceRecord\InvoiceRecordCategories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class InvoiceRecordQuickAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accountReceivableId', HiddenType::class)
            ->add('category', ChoiceType::class, [
                'required' => false,
                'choice_translation_domain' => false,
                'choices' => InvoiceRecordCategories::getCategories(),
                'attr' => ['class' => 'field-to-disable'],
            ])
            ->add('expectedPaymentDate', DatePickerType::class)
            ->add('active', CheckboxType::class, [
                'required' => false,
                'attr' => ['class' => 'disable-field'],
            ])
        ;
    }
}
