# ShortDescription Resolver Pattern

A strategy pattern for resolving a human-readable short description from any resource object.
Allows normalizers to delegate description computation to dedicated resolver classes,
keeping each resolver focused on a single resource type.

---

## Interface

📁 `App\Serializer\Resolver\ShortDescriptionResolverInterface`

```php
#[AutoconfigureTag('app.short_description_resolver')]
interface ShortDescriptionResolverInterface
{
    public function supports(object $resource): bool;

    public function resolve(object $resource): ?string;
}
```

The tag `app.short_description_resolver` is declared directly on the interface via
`#[AutoconfigureTag]` — any implementing class is auto-registered by Symfony with no
additional configuration.

---

## Creating a resolver

Create a class in `App\Serializer\Resolver\` that implements the interface:

```php
class MyEntityShortDescriptionResolver implements ShortDescriptionResolverInterface
{
    public function supports(object $resource): bool
    {
        return $resource instanceof MyEntity;
    }

    public function resolve(object $resource): ?string
    {
        return $resource->getTitle();
    }
}
```

---

## Using resolvers

Inject the full resolver chain via `#[AutowireIterator]` and iterate until a resolver
matches the resource — the first match wins:

```php
public function __construct(
    #[AutowireIterator(tag: 'app.short_description_resolver')]
    private readonly iterable $resolvers,
) {}

// ...

foreach ($this->resolvers as $resolver) {
    if ($resolver->supports($resource)) {
        return $resolver->resolve($resource);
    }
}
```
