<?php

declare(strict_types=1);

namespace App\AI\Filterable;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class FilterableRegistry
{
    /** @var array<string, AbstractFilterableDefinition> */
    private array $providers;

    /**
     * @param iterable<AbstractFilterableDefinition> $providers Concrete definitions, auto-tagged via the base class's `#[AutoconfigureTag('ai.filterable')]`
     */
    public function __construct(
        #[AutowireIterator('ai.filterable')]
        iterable $providers,
    ) {
        $indexed = [];
        foreach ($providers as $provider) {
            $indexed[$provider->name()] = $provider;
        }
        $this->providers = $indexed;
    }

    /**
     * @return array<string, AbstractFilterableDefinition>
     */
    public function all(): array
    {
        return $this->providers;
    }

    public function get(string $name): ?AbstractFilterableDefinition
    {
        return $this->providers[$name] ?? null;
    }

    /**
     * Validates that all keys in $filters are declared in the provider's schema.
     *
     * @param array<string, mixed> $filters
     *
     * @return string|null Error message if invalid, null if valid
     */
    public function validateFilters(AbstractFilterableDefinition $provider, array $filters): ?string
    {
        $allowed = array_map(static fn (FilterableField $f) => $f->name, $provider->fieldSchema());
        $unknown = array_diff(array_keys($filters), $allowed);

        if ([] === $unknown) {
            return null;
        }

        return \sprintf(
            'Unknown filter(s) for entity "%s": %s. Allowed filters: %s',
            $provider->name(),
            implode(', ', $unknown),
            implode(', ', $allowed),
        );
    }
}
