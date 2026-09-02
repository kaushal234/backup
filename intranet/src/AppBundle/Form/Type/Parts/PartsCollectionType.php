<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartsCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parts', CollectionType::class, [
                'entry_type' => PartsType::class,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
                'attr' => ['class' => 'no-js'],
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
            ])
            ->add('submit', SubmitType::class, [
                'translation_domain' => 'messages',
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
            ->add('sparePartsRequest', SparePartsRequestChoiceType::class, [
                'label' => false,
                'sparePartsRequest' => $builder->getData(),
                'required' => false,
            ])
            ->add('moveToAnotherSparePartsRequest', SubmitType::class, [
                'label' => 'spare_parts_request.button.move_to_another_spr',
                'attr' => ['class' => 'btn btn-info'],
            ])
            ->add('createNewSparePartRequest', SubmitType::class, [
                'label' => 'spare_parts_request.button.create_new_spr',
                'attr' => ['class' => 'btn btn-warning'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'spare_parts_request',
        ]);
    }
}
