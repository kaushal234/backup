<?php

declare(strict_types=1);

namespace App\Form\Location;

use App\CQRS\Query\Location\FindAllLocationsQuery;
use App\CQRS\QueryBusInterface;
use App\Sdk\Resource\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationChoiceType extends AbstractType
{
    public function __construct(
        private readonly QueryBusInterface $messageBus,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'key' => 'iri',
                'filters' => [],
                'choice_translation_domain' => false,
                'choices' => function (Options $options) {
                    $collection = $this->messageBus->dispatch(new FindAllLocationsQuery(options: ['vendorUserLocations' => true]));
                    $choices = [];
                    /** @var Location $location */
                    foreach ($collection as $location) {
                        $choices[$location->name.' ('.$location->erp.')'] = $location->{$options['key']};
                    }

                    return ['ALL' => 'ALL'] + $choices;
                },
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
