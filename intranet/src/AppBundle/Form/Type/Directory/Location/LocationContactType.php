<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationContactType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('telephone', TextType::class, [
                'label' => 'directory.location_contact.fields.telephone',
                'required' => false,
            ])
            ->add('fax', TextType::class, [
                'label' => 'directory.location_contact.fields.fax',
                'required' => false,
            ])
            ->add('sparePartsEmail', TextType::class, [
                'label' => 'directory.location_contact.fields.sparePartsEmail',
                'required' => false,
            ])
            ->add('partsCustomerSupportEmail', TextType::class, [
                'label' => 'directory.location_contact.fields.partsCustomerSupportEmail',
                'required' => false,
            ])
            ->add('sparePartsTelephone', TextType::class, [
                'label' => 'directory.location_contact.fields.sparePartsTelephone',
                'required' => false,
            ])
            ->add('sparePartsFax', TextType::class, [
                'label' => 'directory.location_contact.fields.sparePartsFax',
                'required' => false,
            ])
            ->add('serviceHubEmail', TextType::class, [
                'label' => 'directory.location_contact.fields.serviceHubEmail',
                'required' => false,
            ])
            ->add('serviceHubTelephone', TextType::class, [
                'label' => 'directory.location_contact.fields.serviceHubTelephone',
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
        return 'app_location_contact';
    }
}
