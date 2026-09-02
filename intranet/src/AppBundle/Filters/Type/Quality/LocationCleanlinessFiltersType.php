<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Quality;

use ApiBundle\Client;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class LocationCleanlinessFiltersType extends AbstractType
{
    private readonly Client $client;

    private readonly DataProvider $dataProvider;

    public function __construct(Client $client, DataProvider $dataProvider)
    {
        $this->client = $client;
        $this->dataProvider = $dataProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->client->get('/me');
        $collection = $this->dataProvider->findAll('locations');
        $choices = [];
        foreach ($collection as $location) {
            if (!empty($location['capability']['factory']) || !empty($location['capability']['sparePartsHub'])) {
                $value = $location['name'];
                $choices[$value] = $location['@id'];
            }
        }

        ksort($choices);

        $defaultLocation = $user['businessUnit']['location']['@id'] ?? null;

        $builder
            ->add('location', LocationChoiceType::class, [
                'label' => 'cleanliness.fields.location',
                'required' => false,
                'data' => \in_array($defaultLocation, $choices, true) ? $defaultLocation : null,
                'empty_data' => \in_array($defaultLocation, $choices, true) ? $defaultLocation : null,
                'choices' => $choices,
            ])
            ->add('date-after', DatePickerType::class, [
                'label' => 'cleanliness.fields.startingDate',
                'property_path' => '[date][after]',
                'required' => true,
            ])
            ->add('date-before', DatePickerType::class, [
                'label' => 'cleanliness.fields.endingDate',
                'property_path' => '[date][before]',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'cleanliness',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_location_cleanliness';
    }
}
