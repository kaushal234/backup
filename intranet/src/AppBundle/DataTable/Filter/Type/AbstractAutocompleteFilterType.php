<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type;

use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterView;
use Kreyu\Bundle\DataTableBundle\Filter\Type\FilterTypeInterface;
use Kreyu\Bundle\DataTableBundle\Util\StringUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractAutocompleteFilterType implements FilterTypeInterface
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
    }

    public function buildView(FilterView $view, FilterInterface $filter, FilterData $data, array $options): void
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    public function getBlockPrefix(): string
    {
        return StringUtil::fqcnToShortName(static::class, ['FilterType', 'Type']) ?: '';
    }

    public function getParent(): ?string
    {
        return AutocompleteFilterType::class;
    }
}
