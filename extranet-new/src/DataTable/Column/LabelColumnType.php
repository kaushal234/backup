<?php

declare(strict_types=1);

namespace App\DataTable\Column;

use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

class LabelColumnType extends AbstractColumnType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildValueView(ColumnValueView $view, ColumnInterface $column, array $options): void
    {
        $rowData = $view->getRowData();

        if (\is_callable($textValues = $options['text_values'])) {
            $textValues = $textValues($view->parent->data, $column);
        }

        if (\is_callable($labelClasses = $options['label_classes'])) {
            $labelClasses = $labelClasses($view->parent->data, $column);
        }

        if ((\is_string($options['color_property_path']) || $options['color_property_path'] instanceof PropertyPathInterface) && (\is_array($rowData) || \is_object($rowData))) {
            $color = $options['property_accessor']->getValue($rowData, $options['color_property_path']);
        }

        $view->vars = array_merge($view->vars, [
            'text_values' => $textValues,
            'label_classes' => $labelClasses,
            'color' => $color ?? null,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'template_path' => 'bundles/KreyuDataTableBundle/column/label.html.twig',
                'text_values' => [],
                'label_classes' => [],
            ])
            ->setAllowedTypes('text_values', ['array', 'callable'])
            ->setAllowedTypes('label_classes', ['array', 'callable'])
            ->setInfo('text_values', 'Map original values into custom texts.')
            ->setInfo('label_classes', 'Match original values with label classes.')
        ;

        $resolver->define('color_property_path')
            ->default(null)
            ->allowedTypes('null', 'bool', 'string', PropertyPathInterface::class)
            ->info('Path to use by property accessor component to retrieve the color value from parent row data.')
        ;
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }
}
