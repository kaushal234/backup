<?php

declare(strict_types=1);

namespace App\Form\PurchaseOrder;

use App\DataTransferObject\PurchaseOrder\EditAllPurchaseOrderLine;
use Psl\Str;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditAllLinesType extends AbstractType
{
    public static function createName(): string
    {
        return Str\format('edit_all_lines');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('editLines', CollectionType::class, [
                'entry_type' => EditLineType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_delete' => true,
                'label' => false,
            ])
        ;

        if (true === $options['message_required']) {
            $builder->add('message', TextareaType::class, [
                'empty_data' => '',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EditAllPurchaseOrderLine::class,
            'message_required' => true,
        ]);
    }
}
