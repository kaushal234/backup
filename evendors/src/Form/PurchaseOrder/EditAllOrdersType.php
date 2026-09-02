<?php

declare(strict_types=1);

namespace App\Form\PurchaseOrder;

use App\DataTransferObject\PurchaseOrder\EditAllPurchaseOrder;
use Psl\Str;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditAllOrdersType extends AbstractType
{
    public static function createName(): string
    {
        return Str\format('edit_all_orders');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('editPurchaseOrders', CollectionType::class, [
                'entry_type' => EditAllLinesType::class,
                'entry_options' => [
                    'label' => false,
                    'message_required' => false,
                ],
                'label' => false,
            ])
            ->add('message', TextareaType::class, [
                'empty_data' => '',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'PO# XXXXXX : message....'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EditAllPurchaseOrder::class,
        ]);
    }
}
