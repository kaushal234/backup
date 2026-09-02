<?php

declare(strict_types=1);

namespace App\Form\PurchaseOrder;

use Psl\Str;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PopulateColumnType extends AbstractType
{
    public static function createName(): string
    {
        return Str\format('confirmed_delivery_date_select');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('populateColumn', ChoiceType::class, [
                'choices' => [
                    'purchase_order.confirm.populate_column' => 'initial-date-value',
                    'display.table.purchase_order_line.headers.delivery_date' => 'planned-delivery-date-value',
                    'display.table.purchase_order_line.headers.rescheduled_delivery_date' => 'rescheduled-delivery-date-value',
                ],
                'translation_domain' => 'messages',
                'label' => false,
                'disabled' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
