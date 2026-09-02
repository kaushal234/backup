<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\CheckboxColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VisibleCheckboxColumnType extends AbstractColumnType
{
    public function buildValueView(ColumnValueView $view, ColumnInterface $column, array $options): void
    {
        if (\is_callable($enable = $options['enable'])) {
            $enable = $enable($view->parent->data);
        }
        $view->vars['enable'] = $enable;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define('enable')
            ->default(true)
            ->allowedTypes('boolean', 'callable')
            ->info('The capacity to enable or not the checkbox.');
    }

    public function getParent(): ?string
    {
        return CheckboxColumnType::class;
    }
}
