<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\ColumnInterface;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SparePartsRequestParentColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildValueView(ColumnValueView $view, ColumnInterface $column, array $options): void
    {
        $row = $view->parent->data;
        $type = $row['@type'] ?? null;

        $view->vars['spr_type'] = $type;
        $view->vars['toc_id'] = $row['tocId'] ?? null;
        $view->vars['sb_id'] = $row['sbId'] ?? null;
        $view->vars['href'] = match ($type) {
            'TocSparePartsRequest' => isset($row['tocId'])
                ? $this->urlGenerator->generate('legacy_service', ['m' => ['toc', 'view', 'parts'], 'id' => $row['tocId']])
                : null,
            'SbSparePartsRequest' => isset($row['sbId'])
                ? $this->urlGenerator->generate('legacy_product_support', ['m' => ['sb', 'view'], 'id' => $row['sbId']])
                : null,
            default => null,
        };
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'spare_parts_request.fields.parent',
            'header_translation_domain' => 'spare_parts_request',
            'template_path' => 'parts/spare_parts_requests/datatable/column/parent.html.twig',
            'sort' => false,
        ]);
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }
}
