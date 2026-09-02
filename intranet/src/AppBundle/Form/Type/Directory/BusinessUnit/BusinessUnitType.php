<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\BusinessUnit;

use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Region\RegionChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessUnitType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'directory.business_unit.fields.name',
                'required' => true,
            ])
            ->add('representative', PeopleAutocompleteChoiceType::class, [
                'label' => 'directory.business_unit.fields.representative',
                'required' => false,
                'placeholder' => 'directory.business_unit.make_selection',
            ])
            ->add('location', LocationChoiceType::class, [
                'label' => 'directory.business_unit.fields.location',
                'required' => true,
                'placeholder' => 'directory.business_unit.make_selection',
            ])
            ->add('domain', TextType::class, [
                'label' => 'directory.business_unit.fields.domain',
                'required' => false,
                'help' => 'directory.business_unit.helper.domain',
            ])
            ->add('region', RegionChoiceType::class, [
                'label' => 'directory.region.name',
                'required' => true,
                'placeholder' => 'directory.business_unit.make_selection',
            ])
            ->add('disabled', CheckboxType::class, [
                'label' => 'directory.user.fields.disabled',
                'required' => false,
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
        return 'app_business_unit';
    }
}
