<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Position;

use AppBundle\Form\Type\Directory\Division\DivisionPositionCategoryType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionCategoryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'fields.name',
                'translation_domain' => 'messages',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'fields.description',
                'translation_domain' => 'messages',
                'required' => true,
                'attr' => ['maxlength' => 500, 'style' => 'resize:vertical; height:70px'],
            ])
            ->add('positionCategoryType', PositionCategoryTypeChoiceType::class, [
                'label' => 'directory.position_category_types.singular',
                'required' => false,
            ])
            ->add('directHeadcount', ChoiceType::class, [
                'label' => 'directory.position_categories.direct_headcount',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'choices' => ['yes' => true, 'no' => false],
                'choice_translation_domain' => 'messages',
            ])
        ;

        if ($options['add']) {
            $builder->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ]);
        }

        if (!$options['add']) {
            $builder->add('divisions', CollectionType::class, [
                'entry_type' => DivisionPositionCategoryType::class,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
            'add' => false,
        ]);
    }
}
