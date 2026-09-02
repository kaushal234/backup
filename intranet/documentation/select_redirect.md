# Select Redirect

This documentation explains how to **redirect to a route when an option is selected** in a select / [autocomplete](autocomplete.md).

## Description

When the user picks an option, the page navigates to a route built from the selected item.

It replaces the old "assign then redirect" pattern (select a user → the entity is updated and the page reloads on the target).

The whole selected object is available, so any of its properties can be mapped to a route parameter.

---

## How to Use It

Configure the option directly on your **select form type** (it is available on every `SelectFormType`, autocomplete included):

```php
$builder->add('people', PeopleAutocompleteChoiceType::class, [
    'redirect_route' => 'directory_people_show',
    'redirect_route_params_map' => ['id' => 'id'],
]);
```

Three options are available:

- `redirect_route`: the route name to redirect to (enables the feature).
- `redirect_route_params_map`: a map of `"<selected data property>" => "<route param>"`.
  Each property is read on the selected item and injected into the route param.
- `redirect_route_params_extra`: static extra params merged into the route (default `[]`).

With the example above, selecting a person redirects to `directory_people_show` with `id` taken from the result's `id` property.

---

## Advanced Usage

Use `redirect_route_params_extra` when the route needs values that don't come from the selected item:

```php
$builder->add('assignee', MISChoiceType::class, [
    'redirect_route' => 'trouble_ticket_assign_to_team',
    'redirect_route_params_map' => ['id' => 'assigneeId'],
    'redirect_route_params_extra' => ['id' => troubleTicketId],
]);
```

Notes:

- The mapped property must be returned by the API for that result (check your normalization groups). `@id` gives the full IRI; map a numeric `id` when the route expects one.
- The target route must be **exposed to the JS router** (`options: ['expose' => true]` on the `#[Route]`), otherwise you get `The route "..." does not exist`.

See also: [autocomplete.md](autocomplete.md) · [select_cascading.md](select_cascading.md).
