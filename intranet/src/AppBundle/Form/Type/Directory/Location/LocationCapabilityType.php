<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationCapabilityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sso', CheckboxType::class, [
                'label' => 'directory.location_capability.fields.sso',
            ])
            ->add('factory', CheckboxType::class, [
                'label' => 'directory.location_capability.fields.factory',
            ])
            ->add('warehouse', CheckboxType::class, [
                'label' => 'directory.location_capability.fields.warehouse',
            ])
            ->add('sparePartsHub', CheckboxType::class, [
                'label' => 'directory.location_capability.fields.sparePartsHub',
            ])
            ->add('serviceHub', CheckboxType::class, [
                'label' => 'directory.location_capability.fields.serviceHub',
            ])
            ->add('headQuarter', CheckboxType::class, [
                'label' => 'directory.location_capability.fields.headQuarter',
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
        return 'app_location_capability';
    }
}
