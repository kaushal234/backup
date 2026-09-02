<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
abstract class AbstractLocationChoiceType extends AbstractType
{
    public function __construct(
        private readonly DataProvider $dataProvider,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * {@inheritdoc}
     */
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
            'erp_in_label' => false,
            'currency_in_label' => false,
            'choice_translation_domain' => false,
            'normalizationGroups' => '',
            'choices' => function (Options $options): array {
                $collection = $this->dataProvider->findAll(
                    'locations',
                    $options['filters'],
                    ['name']
                );

                $choices = [];
                foreach ($collection as $location) {
                    $value = $location['name'];
                    $value = $options['erp_in_label'] ? \sprintf('%s - %s', $location['name'], $location['erp']) : $value;
                    $value = $options['currency_in_label'] ? \sprintf('%s - %s', $location['name'], $location['currency']['name'] ?? 'N/A') : $value;

                    $choices[$value] = $location[$options['key']];
                }

                return array_merge($options['extra_choices'], $choices);
            },
            'attr' => [
                'data-no_results_text' => $this->translator->trans('no_results', [], 'messages'),
            ],
        ])->addNormalizer('filters', static fn (Options $options, $value) => $value + ['normalizationGroupsOverride' => ['location_public', $options['normalizationGroups']]]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return SelectFormType::class;
    }
}
