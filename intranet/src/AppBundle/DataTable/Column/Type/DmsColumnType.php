<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DmsColumnType extends AbstractColumnType
{
    public function buildValueView(ColumnValueView $view, ColumnInterface $column, array $options): void
    {
        $view->vars = array_replace($view->vars, [
            'route' => $options['route'],
            'key' => $options['key'],
            'extra_params' => $options['extra_params'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'template_path' => 'bundles/KreyuDataTableBundle/column/dms.html.twig',
                'route' => 'legacy_mis',
                'key' => 'id',
                'extra_params' => ['m' => ['help', 'dms']],
            ])
            ->setAllowedTypes('route', 'string')
            ->setAllowedTypes('key', 'string')
            ->setAllowedTypes('extra_params', 'array')
            ->setRequired('route')
        ;
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }
}
