# Cascading Select

This documentation explains how to use the **autocomplete select** with cascading behavior.

## Description

The cascading feature allows you to connect two select fields together.

When a value is selected in the first select, the second one is automatically filtered based on that selection.

For example:

- First select: **Model**
- Second select: **Product**

When selecting a model, the product select will automatically display only the products related to the selected model.

---

## How to Use It

You only need to configure your **Symfony form type**.

For example:

```php
$builder
    ->add('model', ModelAutocompleteChoiceType::class, [
        'label' => 'Model',
    ])
    ->add('product', ProductAutocompleteChoiceType::class, [
        'label' => 'Product',
    ]);
```

At this stage, both fields are independent.

To enable cascading, you need to configure two options on the first field:

- `cascading_target_form`: the name of the target field
- `cascading_to_filter`: the filter name applied to the target autocomplete

```php
$builder
    ->add('model', ModelAutocompleteChoiceType::class, [
        'label' => 'Model',
        'cascading_target_form' => 'product',
        'cascading_to_filter' => 'model',
    ])
    ->add('product', ProductAutocompleteChoiceType::class, [
        'label' => 'Product',
    ]);
```

With this configuration:

- When a model is selected,
- A filter named `model` is automatically applied to the `product` autocomplete,
- The selected model IRI is sent as the filter value,
- The product select reloads with the filtered results.

---

## Advanced Usage

By default, the IRI of the selected entity is used as the filter value.

If you prefer to use a specific property from the selected entity instead, you can use the `cascading_from_property` option.

```php
$builder
    ->add('model', ModelAutocompleteChoiceType::class, [
        'label' => 'Model',
        'cascading_target_form' => 'product',
        'cascading_from_property' => 'country',
        'cascading_to_filter' => 'model.country',
    ])
    ->add('product', ProductAutocompleteChoiceType::class, [
        'label' => 'Product',
    ]);
```

With this configuration:

- The `country` property of the selected Model is extracted,
- A filter named `model.country` is applied to the Product autocomplete,
- Only products associated with the selected country are displayed.