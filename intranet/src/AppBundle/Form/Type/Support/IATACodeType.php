<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use AppBundle\Form\Type\CountryChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IATACodeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'support.iata.fields.code',
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'support.iata.fields.type',
                'choices' => [
                    'Airport' => 'Airport',
                    'Railway Station' => 'Railway Station',
                    'Bus Station' => 'Bus Station',
                    'Off-Line Point' => 'Off-Line Point',
                    'Metropolitan Area' => 'Metropolitan Area',
                    'Ferry Port' => 'Ferry Port',
                    'Heliport' => 'Heliport',
                ],
                'required' => true,
            ])
            ->add('cityName', TextType::class, [
                'label' => 'support.iata.fields.city_name',
                'required' => true,
            ])
            ->add('cityCode3', TextType::class, [
                'label' => 'support.iata.fields.city_code',
                'required' => false,
            ])
            ->add('state', TextType::class, [
                'label' => 'support.iata.fields.state',
                'required' => false,
            ])
            ->add('country', CountryChoiceType::class, [
                'label' => 'support.iata.fields.country',
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'support.iata.fields.name',
                'required' => false,
            ])
            ->add('source', ChoiceType::class, [
                'label' => 'support.iata.fields.source',
                'choices' => [
                    'IATA' => 'IATA',
                    'TLD' => 'TLD',
                ],
                'required' => true,
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'support.iata.fields.latitude',
                'required' => false,
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'support.iata.fields.longitude',
                'required' => false,
            ])
        ;
        $builder->get('country')
            ->addModelTransformer(new CallbackTransformer(
                static fn ($country) => null === $country ? '' : $country,
                static fn ($country) => '' === $country ? null : $country
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'support',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_iata_codes';
    }
}
