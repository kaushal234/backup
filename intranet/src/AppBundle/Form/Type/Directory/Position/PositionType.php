<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Position;

use ApiBundle\Form\Type\ResourceCollectionType;
use AppBundle\Form\Type\Directory\Division\DivisionGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'directory.position.fields.code',
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'label' => 'directory.position.fields.description',
                'required' => true,
            ])
            ->add('level', ResourceCollectionType::class, [
                'label' => 'directory.position.fields.level',
                'required' => true,
                'resource' => 'position_levels',
                'property' => 'label',
            ])
            ->add('mentorMandatory', CheckboxType::class, [
                'label' => 'directory.position.fields.is_mentor_mandatory',
                'required' => false,
            ])
            ->add('divisionGroups', CollectionType::class, [
                'entry_type' => DivisionGroupType::class,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_position';
    }
}
