<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street1', TextType::class, [
                'property_path' => $options['path_prefix'].'[street1]',
                'label' => 'address.fields.street1',
                'required' => true,
            ])
            ->add('street2', TextType::class, [
                'property_path' => $options['path_prefix'].'[street2]',
                'label' => 'address.fields.street2',
                'required' => false,
            ])
            ->add('postalCode', TextType::class, [
                'property_path' => $options['path_prefix'].'[postalCode]',
                'label' => 'address.fields.postal_code',
                'required' => true,
            ])
            ->add('town', TextType::class, [
                'property_path' => $options['path_prefix'].'[town]',
                'label' => 'address.fields.town',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'property_path' => $options['path_prefix'].'[city]',
                'label' => 'address.fields.city',
                'required' => true,
            ])
            ->add('state', TextType::class, [
                'property_path' => $options['path_prefix'].'[state]',
                'label' => 'address.fields.state',
                'required' => false,
            ])
        ;

        if (false !== $options['country']) {
            $builder
                ->add('country', CountryType::class, [
                    'property_path' => $options['path_prefix'].'[country]',
                    'label' => 'address.fields.country',
                    'required' => true,
                    'placeholder' => 'address.make_selection',
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'country' => true,
            'path_prefix' => '',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_address';
    }
}
