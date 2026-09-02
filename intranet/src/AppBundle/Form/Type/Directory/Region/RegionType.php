<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Region;

use AppBundle\Form\Type\Directory\Division\SubDivisionChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'directory.region.fields.name',
                'required' => true,
            ])
            ->add('representative', PeopleAutocompleteChoiceType::class, [
                'label' => 'directory.location.fields.representative',
                'required' => false,
                'placeholder' => 'directory.location.make_selection',
            ])
            ->add('subDivision', SubDivisionChoiceType::class, [
                'label' => 'directory.sub_division.name',
                'required' => true,
                'placeholder' => 'directory.business_unit.make_selection',
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
}
