<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type;

use ApiBundle\Client;
use AppBundle\DataTable\Filter\Formatter\AutocompleteFilterFormatter;
use AppBundle\DataTable\Filter\Handler\ApiFilterHandler;
use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterView;
use Kreyu\Bundle\DataTableBundle\Filter\Form\Type\OperatorType;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterTypeInterface;
use Kreyu\Bundle\DataTableBundle\Util\StringUtil;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatableInterface;

final class AutocompleteFilterType implements FilterTypeInterface
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $setters = [
            'form_type' => $builder->setFormType(...),
            'form_options' => $builder->setFormOptions(...),
            'operator_form_type' => $builder->setOperatorFormType(...),
            'operator_form_options' => $builder->setOperatorFormOptions(...),
            'default_operator' => $builder->setDefaultOperator(...),
            'supported_operators' => $builder->setSupportedOperators(...),
            'operator_selectable' => $builder->setOperatorSelectable(...),
        ];

        foreach ($setters as $option => $setter) {
            $setter($options[$option]);
        }

        $builder->setHandler(new ApiFilterHandler());
    }

    public function buildView(FilterView $view, FilterInterface $filter, FilterData $data, array $options): void
    {
        $value = null;
        $isMultiple = $filter->getConfig()->getFormOptions()['multiple'] ?? false;

        if ($data->hasValue()) {
            $value = $data->getValue();
            if ($formatter = $options['active_filter_formatter']) {
                $value = $formatter($data, $filter, $options);
            }
        }

        // Usefull to create clear url filter.
        if (null === $value && $isMultiple) {
            $data->setValue([]);
        }

        $view->data = $data;
        $view->value = $value;

        $view->vars = array_replace($view->vars, [
            'name' => $filter->getName(),
            'query_path' => $filter->getQueryPath(),
            'label' => $options['label'] ?? StringUtil::camelToSentence($filter->getName()),
            'label_translation_parameters' => $options['label_translation_parameters'],
            'translation_domain' => $options['translation_domain'] ?? $view->parent->vars['translation_domain'] ?? null,
            'form_type' => $filter->getConfig()->getFormType(),
            'form_options' => $filter->getConfig()->getFormOptions(),
            'operator_form_type' => $filter->getConfig()->getOperatorFormType(),
            'operator_form_options' => $filter->getConfig()->getOperatorFormOptions(),
            'operator_selectable' => $filter->getConfig()->isOperatorSelectable(),
            'default_operator' => $filter->getConfig()->getDefaultOperator(),
            'supported_operators' => $filter->getConfig()->getSupportedOperators(),
            'data' => $view->data,
            'value' => $view->value,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => null,
                'label_translation_parameters' => [],
                'translation_domain' => null,
                'query_path' => null,
                'form_type' => TextType::class,
                'form_options' => [],
                'operator_form_type' => OperatorType::class,
                'operator_form_options' => [],
                'default_operator' => Operator::Equals,
                'supported_operators' => [],
                'operator_selectable' => false,
                'active_filter_formatter' => null,
            ])
            ->setAllowedTypes('label', ['null', 'bool', 'string', TranslatableInterface::class])
            ->setAllowedTypes('label_translation_parameters', 'array')
            ->setAllowedTypes('translation_domain', ['null', 'bool', 'string'])
            ->setAllowedTypes('query_path', ['null', 'string'])
            ->setAllowedTypes('form_type', 'string')
            ->setAllowedTypes('form_options', 'array')
            ->setAllowedTypes('operator_form_type', 'string')
            ->setAllowedTypes('operator_form_options', 'array')
            ->setAllowedTypes('default_operator', Operator::class)
            ->setAllowedTypes('supported_operators', Operator::class.'[]')
            ->setAllowedTypes('operator_selectable', 'bool')
            ->setAllowedTypes('active_filter_formatter', ['null', 'callable'])
            ->setAllowedValues('translation_domain', static function (mixed $value): bool {
                return null === $value || false === $value || \is_string($value);
            })
            ->setNormalizer('active_filter_formatter', function (Options $options, $activeFilter) {
                return new AutocompleteFilterFormatter($this->client, $activeFilter);
            })
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'filter';
    }

    public function getParent(): ?string
    {
        return null;
    }
}
