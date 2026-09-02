<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\MarketIntelligence;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class MarketIntelligenceTypeChoiceType extends AbstractType
{
    public function __construct(
        private readonly DataProvider $dataProvider)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new IrisResourceToIdTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'key' => '@id',
            'filters' => [],
            'extra_choices' => [],
            'choice_translation_domain' => false,
            'choices' => function (Options $options): array {
                $filters = $options['filters'];
                $collection = $this->dataProvider->findAll(
                    'sales/market_intelligence_types',
                    $filters,
                    ['name']
                );

                $choices = [];
                foreach ($collection as $marketIntelligenceType) {
                    $value = $marketIntelligenceType['name'];
                    $choices[$value] = $marketIntelligenceType[$options['key']];
                }

                return array_merge($options['extra_choices'], $choices);
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
