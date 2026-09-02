<?php

declare(strict_types=1);

namespace App\Form\PurchaseOrder;

use App\Sdk\Resource\PurchaseOrderLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LabelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lineIdentifier', TextType::class, [
                'disabled' => true,
                'label' => false,
            ])
            ->add('partNumber', TextType::class, [
                'disabled' => true,
                'label' => false,
            ])
            ->add('quantityLabel', IntegerType::class, [
                'label' => false,
                'error_bubbling' => true,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('labelDeliveredQuantity', IntegerType::class, [
                'label' => false,
                'error_bubbling' => true,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('packingSlip', TextType::class, [
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrderLine::class,
        ]);
    }
}
