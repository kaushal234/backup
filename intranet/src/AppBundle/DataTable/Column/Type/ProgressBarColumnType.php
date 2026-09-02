<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProgressBarColumnType extends AbstractColumnType
{
    public function buildValueView(ColumnValueView $view, ColumnInterface $column, array $options): void
    {
        $color = match (true) {
            $view->value <= 10 => 'danger',
            $view->value <= 25 => 'warning',
            $view->value <= 75 => 'info',
            $view->value <= 99 => 'primary',
            default => 'success',
        };

        $view->vars = array_replace($view->vars, [
            'color' => $color,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'template_path' => 'bundles/KreyuDataTableBundle/column/progress_bar.html.twig',
            ]);
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }
}
