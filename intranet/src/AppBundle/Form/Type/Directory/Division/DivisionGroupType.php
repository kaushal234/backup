<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Division;

use AppBundle\Form\Type\Directory\GroupAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DivisionGroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('division', DivisionChoiceType::class, [
                'label' => 'menu.division.title',
            ])
            ->add('groups', GroupAutocompleteChoiceType::class, [
                'label' => 'directory.position.fields.groups',
                'translation_domain' => 'directory',
                'required' => false,
                'multiple' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
        ]);
    }
}
