<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\ChoiceLoader\AutoCompleteChoiceLoader;
use App\Sdk\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\LazyChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AutocompleteType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Client $client,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $client = $this->client;

        $resolver->setRequired(['route_name', 'resource_class']);
        $resolver->setAllowedTypes('route_name', 'string');
        $resolver->setAllowedTypes('resource_class', 'string');

        $resolver->setDefaults([
            'autocomplete' => true,
            'required' => false,
            'choices' => [],
            'min_characters' => 3,
            'choice_lazy' => true,
            'choice_value' => static function ($choice) {
                if (\is_object($choice) && property_exists($choice, 'id')) {
                    return $choice->id;
                }

                return $choice;
            },
            'choice_label' => static function ($choice) {
                if (\is_object($choice) && property_exists($choice, 'id')) {
                    return (string) $choice->id;
                }

                return '';
            },
        ]);

        $resolver->setNormalizer('autocomplete_url', function ($options) {
            return $this->urlGenerator->generate($options['route_name']);
        });

        $resolver->setNormalizer('choice_loader', static function ($options) use ($client) {
            return new LazyChoiceLoader(new AutoCompleteChoiceLoader($client, $options['resource_class']));
        });

        $resolver->setNormalizer('tom_select_options', static function ($options) {
            $tomSelectOptions = [
                'create' => false,
                'hideSelected' => true,
                'closeAfterSelect' => true,
                'maxOptions' => null,
                // Render the dropdown on <body> so it is not clipped by ancestors
                // with overflow:hidden (e.g. the .info-card containers).
                'dropdownParent' => 'body',
            ];

            if (false === $options['multiple']) {
                $tomSelectOptions['maxItems'] = 1;
            }

            return $tomSelectOptions;
        });
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
