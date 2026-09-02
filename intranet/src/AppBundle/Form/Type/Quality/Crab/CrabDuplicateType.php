<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Crab;

use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CrabDuplicateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('equipmentRecords', EquipmentRecordAutocompleteChoiceType::class, [
                'required' => true,
                'multiple' => true,
                'query' => [
                    'order' => [
                        'serialNumber' => 'ASC',
                    ],
                    'notShipped' => true,
                    'product.family.productType' => $options['productType'],
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'productType' => null,
            'csrf_protection' => false,
        ]);
    }
}
