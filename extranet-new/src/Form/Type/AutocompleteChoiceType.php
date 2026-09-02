<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AutocompleteChoiceType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('autocomplete_url')
            ->setAllowedTypes('autocomplete_url', 'string')
            ->setDefaults([
                'mapped' => false,
                'attr' => [],
                'min_length' => 2,
            ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr'] = array_merge(
            [
                'class' => 'form-control',
                'data-controller' => 'autocomplete',
                'data-autocomplete-url-value' => $this->urlGenerator->generate($options['autocomplete_url']),
                'data-autocomplete-min-length-value' => $options['min_length'],
            ],
            $view->vars['attr']
        );
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
