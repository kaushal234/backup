<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\CQRS\Query\FindAllCountriesQuery;
use App\CQRS\QueryBusInterface;
use App\Sdk\Resource\Country;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryChoiceType extends AbstractType
{
    public function __construct(
        private readonly QueryBusInterface $queryBus
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choice_translation_domain' => false,
                'choices' => function (Options $options) {
                    /** @var Country[] $countries */
                    $countries = $this->queryBus->dispatch(new FindAllCountriesQuery());

                    $choices = [];
                    foreach ($countries as $country) {
                        $choices[$country->name] = $country->iri;
                    }

                    return $choices;
                },
                'placeholder' => '',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
