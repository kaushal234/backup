<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Division;

use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DivisionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $division = $builder->getData();

        $builder
            ->add('name', TextType::class, [
                'label' => 'fields.name',
            ])
            ->add('representatives', PeopleAutocompleteChoiceType::class, [
                'label' => 'directory.division.fields.representatives',
                'translation_domain' => 'directory',
                'required' => false,
                'multiple' => true,
                'placeholder' => null,
                'data' => $division ? $division['representatives'] : null,
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
