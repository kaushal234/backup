<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\MarketIntelligence;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarketIntelligenceAutocompleteChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'market_intelligence.name',
                'translation_domain' => 'market_intelligence',
                'uri' => 'sales/market_intelligences',
                'text_key' => 'shortDescription',
                'query' => [
                    'normalizationGroupsOverride' => ['market_intelligence:list'],
                ],
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
