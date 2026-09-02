<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use ApiBundle\Form\Type\ResourceCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleAddApplicationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('externalModule', ResourceCollectionType::class, [
                'label' => 'External module',
                'resource' => 'external_modules',
                'property' => 'name',
            ])
            ->add('location', ResourceCollectionType::class, [
                'label' => 'location',
                'resource' => 'business_units',
                'property' => 'name',
            ])
            ->add('admin', CheckboxType::class, [
                'label' => 'Administrator',
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
            'groupFilter' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_people_application_add';
    }
}
