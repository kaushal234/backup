<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type;

use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FactorySupplierReportFilter extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'fields.factory',
                'required' => false,
            ])
            ->add('supplierNumber', TextType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_number',
                'required' => false,
                'translation_domain' => 'vendor_warranty_claim',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'csrf_protection' => false,
        ]);
    }
}
