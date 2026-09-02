<?php

declare(strict_types=1);

namespace Tests\AppBundle\Twig\Components\Service\ServiceArea;

use AppBundle\Twig\Components\Service\ServiceArea\AirportSelectorComponent;
use Tests\AppBundle\Twig\Components\LiveComponentTestCase;

class AirportSelectorComponentTest extends LiveComponentTestCase
{
    // -------------------------------------------------------------------------
    // getFilteredAirports()
    // -------------------------------------------------------------------------

    public function testGetFilteredAirportsReturnsEmptyWhenNoCountrySelected(): void
    {
        $this->login('superuser');

        // No 'airports' mock needed: with no country selected, the method
        // should short-circuit and never call the API.
        $component = $this->createLiveComponent(AirportSelectorComponent::class, [
            'initialCountries' => [],
        ])->component();

        self::assertSame([], $component->getFilteredAirports());
    }

    public function testGetFilteredAirportsReturnsAirportsForSelectedCountry(): void
    {
        $this->login('superuser');

        $airports = [
            $this->airportFixture(61, 'BDX', 'Bordeaux', '/countries/1', 'France'),
            $this->airportFixture(62, 'CDG', 'Paris', '/countries/1', 'France'),
        ];

        $this->mockApi('airports', ['hydra:member' => $airports]);

        $component = $this->createLiveComponent(AirportSelectorComponent::class, [
            'initialCountries' => ['/countries/1'],
        ])->component();

        $result = $component->getFilteredAirports();

        self::assertCount(2, $result);
        self::assertSame('BDX', $result[0]['code']);
    }

    public function testGetAirportsGroupedByCountryIncludesOtherGroupForOrphans(): void
    {
        $this->login('superuser');

        $frenchAirports = [
            $this->airportFixture(62, 'CDG', 'Paris', '/countries/1', 'France'),
        ];
        $orphanAirports = [
            $this->airportFixture(99, 'JRS', 'Jerusalem', null, null),
        ];

        $this->mockApi('countries', ['hydra:member' => $frenchAirports]);
        $this->mockApi('exists', ['hydra:member' => $orphanAirports]);

        $component = $this->createLiveComponent(AirportSelectorComponent::class, [
            'initialCountries' => ['/countries/1'],
        ])->component();

        $grouped = $component->getAirportsGroupedByCountry();

        self::assertArrayHasKey('France', $grouped);
        self::assertArrayHasKey('Other', $grouped);
        self::assertCount(1, $grouped['Other']);
    }

    public function testGetAirportsGroupedByCountryOmitsOtherGroupWhenNoOrphans(): void
    {
        $this->login('superuser');

        $frenchAirports = [
            $this->airportFixture(62, 'CDG', 'Paris', '/countries/1', 'France'),
        ];

        $this->mockApi('countries', ['hydra:member' => $frenchAirports]);
        $this->mockApi('exists', ['hydra:member' => []]);

        $component = $this->createLiveComponent(AirportSelectorComponent::class, [
            'initialCountries' => ['/countries/1'],
        ])->component();

        $grouped = $component->getAirportsGroupedByCountry();

        self::assertArrayHasKey('France', $grouped);
        self::assertArrayNotHasKey('Other', $grouped);
    }

    // -------------------------------------------------------------------------
    // toggleAllAirports() — #[LiveAction]
    // -------------------------------------------------------------------------

    public function testToggleAllAirportsChecksAll(): void
    {
        $this->login('superuser');

        $component = $this->createLiveComponent(AirportSelectorComponent::class, [
            'selectedAirports' => ['/airports/61'],
        ])->component();

        $component->toggleAllAirports(['/airports/61', '/airports/62'], true);

        self::assertEqualsCanonicalizing(['/airports/61', '/airports/62'], $component->selectedAirports);
    }

    public function testToggleAllAirportsUnchecksAll(): void
    {
        $this->login('superuser');

        $component = $this->createLiveComponent(AirportSelectorComponent::class, [
            'selectedAirports' => ['/airports/61', '/airports/62', '/airports/99'],
        ])->component();

        $component->toggleAllAirports(['/airports/61', '/airports/62'], false);

        self::assertSame(['/airports/99'], array_values($component->selectedAirports));
    }

    // -------------------------------------------------------------------------
    // setActiveTab() — #[LiveAction]
    // -------------------------------------------------------------------------

    public function testSetActiveTabUpdatesActiveTab(): void
    {
        $this->login('superuser');

        $component = $this->createLiveComponent(AirportSelectorComponent::class, [])->component();

        $component->setActiveTab('France');

        self::assertSame('France', $component->activeTab);
    }

    public function testSetActiveTabIgnoresNullArgument(): void
    {
        $this->login('superuser');

        $component = $this->createLiveComponent(AirportSelectorComponent::class, [
            'activeTab' => 'France',
        ])->component();

        $component->setActiveTab(null);

        self::assertSame('France', $component->activeTab);
    }

    // -------------------------------------------------------------------------
    // areAllAirportsSelected()
    // -------------------------------------------------------------------------

    public function testAreAllAirportsSelectedTrueWhenAllSelected(): void
    {
        $this->login('superuser');

        $component = $this->createLiveComponent(AirportSelectorComponent::class, [
            'selectedAirports' => ['/airports/61', '/airports/62'],
        ])->component();

        $airports = [
            $this->airportFixture(61, 'BDX', 'Bordeaux', '/countries/1', 'France'),
            $this->airportFixture(62, 'CDG', 'Paris', '/countries/1', 'France'),
        ];

        self::assertTrue($component->areAllAirportsSelected($airports));
    }

    public function testAreAllAirportsSelectedFalseWhenSomeMissing(): void
    {
        $this->login('superuser');

        $component = $this->createLiveComponent(AirportSelectorComponent::class, [
            'selectedAirports' => ['/airports/61'],
        ])->component();

        $airports = [
            $this->airportFixture(61, 'BDX', 'Bordeaux', '/countries/1', 'France'),
            $this->airportFixture(62, 'CDG', 'Paris', '/countries/1', 'France'),
        ];

        self::assertFalse($component->areAllAirportsSelected($airports));
    }

    public function testAreAllAirportsSelectedFalseWhenEmpty(): void
    {
        $this->login('superuser');

        $component = $this->createLiveComponent(AirportSelectorComponent::class, [
            'selectedAirports' => [],
        ])->component();

        self::assertFalse($component->areAllAirportsSelected([]));
    }

    // -------------------------------------------------------------------------
    // Fixtures
    // -------------------------------------------------------------------------

    private function airportFixture(int $id, string $code, string $cityName, ?string $countryIri, ?string $countryName): array
    {
        $airport = [
            '@id' => \sprintf('/airports/%d', $id),
            '@type' => 'Airport',
            'id' => $id,
            'code' => $code,
            'cityName' => $cityName,
        ];

        $airport['country'] = null !== $countryIri
            ? ['@id' => $countryIri, '@type' => 'Country', 'name' => $countryName]
            : null;

        return $airport;
    }
}
