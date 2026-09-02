<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Finance;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\ERPLocationChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierKPIFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('erp', ERPLocationChoiceType::class, [
                'placeholder' => 'make_selection',
                'translation_domain' => 'messages',
                'key' => 'erp',
            ])
            ->add('from', DatePickerType::class, [
                'label' => 'finance.suppliers_payment_terms.fields.invoice_paid_date_from',
                'required' => false,
            ])
            ->add('until', DatePickerType::class, [
                'label' => 'finance.suppliers_payment_terms.fields.invoice_paid_date_until',
                'required' => false,
            ])
            ->add('supplier', TextType::class, [
                'label' => 'fields.supplier.number',
                'translation_domain' => 'messages',
                'required' => false,
                'data' => [],
            ])
            ->add('exclude_sister_companies', CheckboxType::class, [
                'label' => 'finance.suppliers_payment_terms.fields.exclude_sister_companies',
                'required' => false,
                'data' => true,
            ])
            ->add('csv', SubmitType::class, [
                'label' => 'button.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-capitalize'],
            ])
        ;

        $builder->get('supplier')
            ->addModelTransformer(new CallbackTransformer(
                static fn ($sunosAsArray): string => implode(',', $sunosAsArray),
                static fn ($sunosAsString) => array_filter(array_map('trim', explode(',', (string) $sunosAsString)))
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'finance',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'method' => Request::METHOD_GET,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
