# ION --- SourceProvider & ResourceSourceProviderInterface

## Overview

This module implements a provider resolution mechanism based on a given
resource class (FQCN).\
It relies on Symfony service tagging and autowiring to dynamically
collect and resolve the appropriate provider.

Components:

-   **ResourceSourceProviderInterface**: Contract that all providers
    must implement.
-   **SourceProvider**: Resolver responsible for selecting the
    appropriate provider based on the supported class.

------------------------------------------------------------------------

## ResourceSourceProviderInterface

**Namespace:** `App\ION\ResourceSourceProvider`

``` php
#[AutoconfigureTag('app.ion.resource_source_provider')]
interface ResourceSourceProviderInterface
```

### Purpose

Each implementation describes how a specific resource should be handled
(read operations, write operations, filters, etc.) and declares whether
it supports a given class via the `supports()` method.

### Methods

-   `getItemReadOperation(): ?string`\
    Returns the item read operation identifier or `null` if unsupported.

-   `getCollectionReadOperation(): ?string`\
    Returns the collection read operation identifier or `null` if
    unsupported.

-   `getResource(): ?string`\
    Returns the resource identifier or `null`.

-   `getUpdateOperation(): string|array|null`\
    Returns update operation definition(s).\
    Can be:

    -   `string` (single operation),
    -   `array` (multiple operations or metadata),
    -   `null` (not supported).

-   `getCreateOperation(): string|array|null`\
    Same logic as `getUpdateOperation()` but for creation.

-   `getDataAreaFilter(): array`\
    Returns filter definitions applied to constrain a data area.\
    Format depends on the consuming layer (e.g., API Platform,
    QueryBuilder, etc.).

-   `deserializeAfterPersist(): bool`\
    Indicates whether the resource should be deserialized after
    persistence.

-   `supports(string $class): bool`\
    Core method. Returns `true` if the provider supports the given fully
    qualified class name (FQCN).

------------------------------------------------------------------------

## SourceProvider (Resolver)

**Namespace:** `App\ION\SourceProvider`

### Constructor & Injection

``` php
public function __construct(
    #[AutowireIterator(tag: 'app.ion.resource_source_provider')]
    private readonly iterable $resourceSourceProviders
) {}
```

Symfony automatically injects all services tagged with
`app.ion.resource_source_provider`.

Because the interface is annotated with:

``` php
#[AutoconfigureTag('app.ion.resource_source_provider')]
```

Any autoconfigured service implementing the interface is automatically
tagged (when `autoconfigure: true` is enabled).

------------------------------------------------------------------------

## Provider Resolution

``` php
public function getResourceSourceProvider(string $class): ResourceSourceProviderInterface
{
    foreach ($this->resourceSourceProviders as $resourceSourceProvider) {
        if ($resourceSourceProvider->supports($class)) {
            return $resourceSourceProvider;
        }
    }

    throw new UnprocessableEntityHttpException(
        sprintf('No supportive Resource Source Provider found for class %s', $class)
    );
}
```

### Behavior

-   Iterates over all registered providers.
-   Returns the first provider for which `supports($class)` returns
    `true`.
-   Throws a `UnprocessableEntityHttpException` (HTTP 422) if none
    match.

### Important Notes

-   The first matching provider wins.
-   If multiple providers support the same class, order matters.
-   Priority management may require tag priorities if deterministic
    ordering is needed.

------------------------------------------------------------------------

## Adding a New ResourceSourceProvider

### Example

``` php
namespace App\ION\ResourceSourceProvider;

final class ProductSourceProvider implements ResourceSourceProviderInterface
{
    public function supports(string $class): bool
    {
        return $class === \App\Entity\Product::class;
    }

    public function getItemReadOperation(): ?string { return 'product_get'; }

    public function getCollectionReadOperation(): ?string { return 'product_list'; }

    public function getResource(): ?string { return 'products'; }

    public function getUpdateOperation(): string|array|null { return 'product_update'; }

    public function getCreateOperation(): string|array|null { return 'product_create'; }

    public function getDataAreaFilter(): array { return []; }

    public function deserializeAfterPersist(): bool { return false; }
}
```

### Service Registration

If autoconfiguration is enabled (default in most Symfony projects), no
manual configuration is required.

Otherwise:

``` yaml
services:
  App\ION\ResourceSourceProvider\ProductSourceProvider:
    tags: ['app.ion.resource_source_provider']
```

------------------------------------------------------------------------

## Best Practices

-   Keep `supports()` deterministic and explicit (prefer strict FQCN
    matching).
-   Return `null` for unsupported operations rather than placeholder
    values.
-   Avoid multiple providers supporting the same class unless ordering
    is explicitly controlled.
-   Ensure error handling (HTTP 422) aligns with your API design
    strategy.

------------------------------------------------------------------------

## Error Handling

If no provider supports the requested class, a
`UnprocessableEntityHttpException` (HTTP 422) is thrown.

This implies that the requested resource class is considered invalid
from a domain perspective.
