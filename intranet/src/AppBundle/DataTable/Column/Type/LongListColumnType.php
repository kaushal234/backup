<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Generic column for displaying a truncated list of items with a full-list modal,
 * optionally grouped by a property in the modal.
 */
class LongListColumnType extends AbstractColumnType
{
    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template_path' => 'components/LongList.html.twig',
        ]);

        $resolver
            ->define('item_formatter')
            ->required()
            ->allowedTypes('callable')
            ->info('Callback transforming a single item into a display string.');

        $resolver
            ->define('limit')
            ->default(8)
            ->allowedTypes('int');

        $resolver
            ->define('separator')
            ->default(', ')
            ->allowedTypes('string');

        $resolver
            ->define('item_sort_key')
            ->default(null)
            ->allowedTypes('null', 'string')
            ->info('Dotted path to sort raw items by, before formatting (e.g. "cityName").');

        $resolver
            ->define('group_by')
            ->default(null)
            ->allowedTypes('null', 'string')
            ->info('Dotted path to group items by in the modal (e.g. "country.name").');
    }

    public function buildValueView(ColumnValueView $view, ColumnInterface $column, array $options): void
    {
        $items = $view->data ?? [];
        $items = is_iterable($items) ? (\is_array($items) ? $items : iterator_to_array($items)) : [];

        if (null !== $options['item_sort_key']) {
            $sortKey = $options['item_sort_key'];
            usort($items, fn (array $a, array $b) => ($this->getNestedValue($a, $sortKey) ?? '') <=> ($this->getNestedValue($b, $sortKey) ?? ''));
        }

        $formatter = $options['item_formatter'];
        $limit = $options['limit'];

        $labels = array_map($formatter, $items);

        $groupedLabels = null;
        if (null !== $options['group_by']) {
            $groupedLabels = [];

            foreach ($items as $item) {
                $groupName = $this->getNestedValue($item, $options['group_by']) ?? 'Other';
                $groupedLabels[$groupName][] = $formatter($item);
            }

            // Sort alphabetically, but keep "Other" pinned last regardless of locale sort order
            uksort($groupedLabels, static function (string $a, string $b): int {
                if ('Other' === $a) {
                    return 1;
                }
                if ('Other' === $b) {
                    return -1;
                }

                return $a <=> $b;
            });
        }

        $view->vars = array_merge($view->vars, [
            'template_path' => $options['template_path'],
            'template_vars' => [
                'labels' => $labels,
                'grouped_labels' => $groupedLabels,
                'limit' => $limit,
                'separator' => $options['separator'],
                'unique_id' => spl_object_id($view),
            ],
        ]);
    }

    /**
     * Reads a value from a nested array using a dotted path (e.g. "country.name"
     * reads $item['country']['name']). Returns null as soon as any step along
     * the way is missing, instead of crashing (e.g. an airport with no country).
     */
    private function getNestedValue(array $item, string $dottedPath): mixed
    {
        $value = $item;

        foreach (explode('.', $dottedPath) as $key) {
            if (!isset($value[$key])) {
                return null;
            }

            $value = $value[$key];
        }

        return $value;
    }
}
