<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\Service\ServiceArea;

use ApiBundle\Client;
use AppBundle\Form\Type\CountryChoiceType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('AirportSelector')]
class AirportSelectorComponent
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public array $selectedAirports = [];

    #[LiveProp]
    public array $initialCountries = [];

    #[LiveProp(writable: true)]
    public ?string $activeTab = null;

    public function __construct(
        private readonly Client $client,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function getFilteredAirports(): array
    {
        $countries = $this->getForm()->get('countries')->getData();

        if (empty($countries)) {
            return [];
        }

        return $this->client->findBy('airports', [
            'country' => $countries,
            'order' => ['code' => 'ASC'],
            'pagination' => false,
        ])->getSimpleArrayCopy();
    }

    public function getAirportsGroupedByCountry(): array
    {
        $airports = $this->getFilteredAirports();

        $grouped = [];
        foreach ($airports as $airport) {
            $countryName = $airport['country']['name'] ?? 'Unknown';
            $grouped[$countryName][] = $airport;
        }

        $orphans = $this->getOrphanAirports();
        if ([] !== $orphans) {
            $grouped['Other'] = $orphans;
        }

        return $grouped;
    }

    #[LiveAction]
    public function toggleAllAirports(#[LiveArg] array $airportIris, #[LiveArg] bool $checked): void
    {
        if ($checked) {
            $this->selectedAirports = array_values(array_unique(array_merge($this->selectedAirports, $airportIris)));
        } else {
            $this->selectedAirports = array_values(array_diff($this->selectedAirports, $airportIris));
        }
    }

    public function areAllAirportsSelected(array $airports): bool
    {
        $iris = array_map(static fn (array $airport) => $airport['@id'], $airports);

        return [] !== $iris && [] === array_diff($iris, $this->selectedAirports);
    }

    public function getOrphanAirports(): array
    {
        return $this->client->findBy('airports', [
            'exists' => ['country' => false],
            'order' => ['code' => 'ASC'],
            'pagination' => false,
        ])->getSimpleArrayCopy();
    }

    #[LiveAction]
    public function setActiveTab(#[LiveArg] ?string $countryName = null): void
    {
        if (null !== $countryName) {
            $this->activeTab = $countryName;
        }
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->createBuilder()
            ->add('countries', CountryChoiceType::class, [
                'required' => false,
                'label' => 'service_area.fields.display_countries',
                'translation_domain' => 'service',
                'multiple' => true,
                'data' => $this->initialCountries,
            ])
            ->getForm();
    }
}
