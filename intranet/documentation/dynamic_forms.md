# Dynamic forms
This documentation explains how to create **dynamic forms** using Symfony UX.

## Description
Dynamic forms are useful when you need to adapt the form structure based on user input — for example, displaying a sub-form depending on the value of a previous field.

This guide demonstrates how to implement dynamic forms using **[Symfony UX Live Components](https://symfony.com/bundles/ux-live-component/current/index.html#forms)**, without writing **any JavaScript**.
## How to Use
All the logic lives inside your Symfony form type by leveraging form events.

Let’s create a simple example: a form that allows a user to rate a ticket.

```php
public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder->add('rating', ChoiceType::class, [
        'choices' => [
            'Select a rating' => null,
            'Great' => 5,
            'Good' => 4,
            'Okay' => 3,
            'Bad' => 2,
            'Terrible' => 1
        ],
    ]);
}
```

This form displays a select field. If the rating is negative (lower than 3), we want to display a textarea field so the user can leave a comment.

To achieve this, we use the `POST_SUBMIT` [form event](https://symfony.com/doc/current/form/dynamic_form_modification.html). Symfony UX Live Components automatically detect changes and submit the form via **AJAX** on each update.

```php
$builder->get('rating')->addEventListener(
    FormEvents::POST_SUBMIT,
    static function (FormEvent $event): void {
        $rating = $event->getForm()->getData();
        $form = $event->getForm()->getParent();

        if (null === $rating || $rating >= 3) {
            return;
        }

        $form->add('comment', TextareaType::class, [
            'label' => 'What went wrong?',
        ]);
    }
);
```

Now, when a rating lower than 3 is selected, a textarea field will dynamically appear.

## Twig Integration

On the Twig side, wrap your form inside the **DynamicForm Live Component**:

```html
<twig:DynamicForm formClass="{{ formClass }}" form="{{ form }}">
    {{ form_start(form) }}
        {{ form_widget(form) }}
    {{ form_end(form) }}
</twig:DynamicForm>
```

* `form` is the FormView
* `formClass` is the fully-qualified class name of the form type

You need to pass both variables from your **controller**:

```php
$form = $this->createForm(RatingType::class);

return $this->render('add_notation.html.twig', [
    'formClass' => RatingType::class,
    'form' => $form->createView(),
]);
```

That’s it! Your form is now dynamically updated without any custom JavaScript.