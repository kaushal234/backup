<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Demo;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class DemoChoiceType extends AbstractType
{
    private readonly DataProvider $dataProvider;

    private readonly TranslatorInterface $translator;

    public function __construct(DataProvider $dataProvider, TranslatorInterface $translator)
    {
        $this->dataProvider = $dataProvider;
        $this->translator = $translator;
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
        $resolver->setDefaults(
            [
                'key' => '@id',
                'filters' => [],
                'attr' => [
                    'data-no_results_text' => $this->translator->trans('no_results', [], 'messages'),
                ],
                'choices' => function (Options $options) {
                    $collection = $this->dataProvider->findAll(
                        'sales/demos',
                        $options['filters'],
                        ['id']
                    );

                    $choices = [];
                    foreach ($collection as $demo) {
                        $value = \sprintf('ID:%s - Customer: %s - Product : %s', $demo['id'], $demo['customer']['name'], $demo['product']['name']);
                        $choices[$value] = $demo[$options['key']];
                    }

                    return $choices;
                },
            ]
        )->addNormalizer('filters', static fn (Options $options, $value) => $value + ['normalization_groups_override' => ['demo_list']]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
