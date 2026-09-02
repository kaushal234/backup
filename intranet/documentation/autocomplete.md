# Autocomplete Select

This documentation explains how to create an **autocomplete select**, a remote select built on top of [TomSelect](https://tom-select.js.org/).

## Description

The autocomplete loads its options from the API as the user types, instead of rendering every choice up front. It is the right tool for large datasets (people, models, products...).

Each result keeps its **full API payload**, not only its id — useful for the related features below.

---

## How to Use It

Create a dedicated **Symfony form type** for your resource. It only needs to extend `AutocompleteChoiceType` and declare its defaults — no `buildForm`, no JavaScript.

> **Naming:** the autocomplete is now the standard select, so there is no need to add `Autocomplete` to the type name. Name it after the resource: `ModelChoiceType`, `ProductChoiceType`...

```php
class ModelChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'uri' => 'models',
            'template' => '{{ name }}',
        ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
```

Then use it like any field:

```php
$builder->add('model', ModelChoiceType::class, [
    'label' => 'Model',
]);
```

### Main options

- `uri` *(required)*: the API endpoint to query, e.g. `models` or `people/search`.
- `template` **or** `text_key`: how each result is displayed (`template` is a Twig/Handlebars string, `text_key` a single property).
- `query`: extra query parameters sent to the API (filters, ordering...).
- `id_key`: property used as the option value (default `@id`).
- `items_per_page`, `min_input_length`, `multiple`, `placeholder`.

```php
$resolver->setDefaults([
    'uri' => 'people/search',
    'query' => [
        'hidden' => false,
        'order' => ['lastname' => 'ASC', 'firstname' => 'ASC'],
    ],
    'template' => '{{ lastname }} {{ firstname }}',
]);
```

### Custom rendering

Three options control how options are displayed. Each accepts a **Twig/Handlebars string** or a **`.html.twig` file path** (the file is sent to the client and rendered by Handlebars, so use `{{ property }}` placeholders referring to the API result):

- `template`: base rendering. Used to render the already selected value (server-side) and as the default for the two options below.
- `js_template_selection`: rendering of the **selected** item in the box. Defaults to `template`.
- `js_template_result`: rendering of each **result in the dropdown** list. Defaults to `js_template_selection` (so ultimately `template`).

In short: set `template` alone for a uniform display, and override `js_template_result` when you want richer results (avatar, email...) than the selected value.

```php
$resolver->setDefaults([
    'uri' => 'people/search',
    // Simple text for the selected value.
    'template' => '{{ lastname }} {{ firstname }}',
    // Richer card for each dropdown result.
    'js_template_result' => 'directory/people/partial/_search.html.twig',
]);
```

---

## Simple Select (no remote)

If you don't need remote loading, use `SelectFormType` directly: it's a regular
`ChoiceType` rendered with TomSelect. You provide the `choices` yourself, exactly
like a native select.

```php
$builder->add('status', SelectFormType::class, [
    'choices' => [
        'Open' => 'open',
        'Closed' => 'closed',
    ],
    'placeholder' => 'Select a status',
]);
```

It supports the usual select options (`multiple`, `placeholder`, `allow_clear`,
`close_on_select`...) and the related features below — but **not** the remote
options (`uri`, `query`, `template`...), which belong to `AutocompleteChoiceType`.

---

## Related Features

Available on any autocomplete (and any `SelectFormType`), through form options only:

- **Redirect on select** — navigate to a route when an option is picked.
  See [select_redirect.md](select_redirect.md).
- **Cascading select** — filter a second select based on the first.
  See [select_cascading.md](select_cascading.md).
