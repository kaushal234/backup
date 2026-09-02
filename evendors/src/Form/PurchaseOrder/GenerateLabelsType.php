<?php

declare(strict_types=1);

namespace App\Form\PurchaseOrder;

use App\Sdk\Resource\PurchaseOrder;
use Psl\Str;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenerateLabelsType extends AbstractType
{
    public static function createName(PurchaseOrder $order): string
    {
        return Str\format('confirm_label');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $builder->getData();
        $confirmableLines = array_values(array_filter(
            $purchaseOrder->lines ?? [],
            static fn ($line) => (bool) ($line->isConfirmable ?? false)
        ));
        $builder->add('lines', CollectionType::class, [
            'mapped' => false,
            'entry_type' => LabelType::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'data' => $confirmableLines,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrder::class,
        ]);
    }
}
