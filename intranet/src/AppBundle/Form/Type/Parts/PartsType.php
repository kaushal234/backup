<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('partNumber', TextType::class, [
                'translation_domain' => 'messages',
                'label' => 'fields.part_number',
            ])
            ->add('description', TextType::class, [
                'translation_domain' => 'messages',
                'label' => 'fields.description',
            ])
            ->add('quantity', NumberType::class, [
                'translation_domain' => 'messages',
                'label' => 'fields.quantity',
            ])
            ->add('unitOfMeasure', TextType::class, [
                'label' => 'spare_parts_request.fields.unit_of_measure',
            ])
            ->add('comment', TextareaType::class, [
                'translation_domain' => 'messages',
                'label' => 'fields.comment',
            ])
            ->add('move', CheckboxType::class, [
                'label' => false,
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
