<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlTooltipColumnType extends AbstractColumnType
{
    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template_path' => 'bundles/KreyuDataTableBundle/column/html_tooltip.html.twig',
        ]);

        $resolver
            ->define('short_text_length')
            ->default(200)
            ->allowedTypes('int');

        $resolver
            ->define('placement')
            ->default('left')
            ->allowedTypes('string');
    }

    public function buildValueView(ColumnValueView $view, ColumnInterface $column, array $options): void
    {
        $view->vars = array_replace($view->vars, [
            'short_text_length' => $options['short_text_length'],
            'placement' => $options['placement'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'html_tooltip';
    }
}
