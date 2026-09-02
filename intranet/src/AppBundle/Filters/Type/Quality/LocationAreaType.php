<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Quality;

use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\Custom\ItemsPerPageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationAreaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'location_areas.fields.name',
                'required' => false,
            ])
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'location_areas.fields.factory_name',
                'translation_domain' => 'location_areas',
                'required' => false,
            ])
            ->add('itemsPerPage', ItemsPerPageType::class)
            ->add('supervisor', PeopleAutocompleteChoiceType::class, [
                'label' => 'location_areas.fields.supervisor',
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
            'translation_domain' => 'location_areas',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_location_area_filters';
    }
}
