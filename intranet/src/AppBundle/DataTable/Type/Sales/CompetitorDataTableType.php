<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Sales;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\Sales\ProductTypeFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ModalActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\CollectionColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\ColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CompetitorDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => 'competitors.fields.id',
                'header_translation_domain' => 'sales_competitors',
                'route' => 'sales_competitors_show',
                'sort' => true,
            ])
            ->addColumn('name', ColumnType::class, [
                'label' => 'competitors.fields.name',
                'header_translation_domain' => 'sales_competitors',
                'sort' => true,
            ])
            ->addColumn('shortDescription', ColumnType::class, [
                'label' => 'competitors.fields.short_description',
                'header_translation_domain' => 'sales_competitors',
            ])
            ->addColumn('url', LinkColumnType::class, [
                'label' => 'competitors.fields.url',
                'header_translation_domain' => 'sales_competitors',
                'href' => static fn (?string $url, ApiData $competitor): string => $competitor['url'] ?? '',
            ])
            ->addColumn('productTypes', CollectionColumnType::class, [
                'label' => 'competitors.fields.product_types',
                'header_translation_domain' => 'sales_competitors',
                'entry_type' => TextColumnType::class,
                'separator' => '<br/>',
                'separator_html' => true,
                'entry_options' => [
                    'formatter' => static fn (array $productTypes) => '- '.$productTypes['englishName'],
                ],
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'sales_competitors_show',
            ])
            ->addRowAction('edit', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $competitor): string {
                    return $this->urlGenerator->generate('sales_competitors_edit', [
                        'id' => $competitor['id'],
                    ]);
                },
                'icon' => 'fa7-solid:edit',
                'visible' => $this->security->isGranted('FEATURE_COMPETITOR_EDIT'),
                'variant' => 'warning',
            ])
            ->addRowAction('delete', ModalActionType::class, [
                'label' => '',
                'route' => 'sales_competitors_delete_confirm',
                'route_params' => static function (ApiData $competitor): array {
                    return [
                        'id' => $competitor['id'],
                    ];
                },
                'icon' => 'fa7-solid:trash',
                'visible' => $this->security->isGranted('FEATURE_COMPETITOR_DELETE'),
                'variant' => 'danger',
            ])
        ;

        $builder
            ->addFilter('productType', ProductTypeFilterType::class, [
                'form_options' => ['multiple' => true],
                'query_path' => 'productTypes',
            ])
        ;

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,name,shortDescription,url,productTypes',
                ],
            ])
        ;

        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'competitors.title',
            'translation_domain' => 'sales_competitors',
        ]);
    }
}
