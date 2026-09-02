<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Support;

use AppBundle\Form\Type\CountryChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IATACodesFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('country', CountryChoiceType::class, [
                'label' => 'support.iata.fields.country',
                'required' => false,
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
                'required' => false,
            ])
            ->add('code', TextType::class, [
                'label' => 'support.iata.fields.code',
                'required' => false,
            ])
            ->add('cityName', TextType::class, [
                'label' => 'support.iata.fields.city_name',
                'required' => false,
            ])
            ->add('cityCode3', TextType::class, [
                'label' => 'support.iata.fields.city_code',
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
            'csrf_protection' => false,
        ]);
    }

    public function getName(): string
    {
        return 'app_support_iata_codes_filters';
    }
}
