<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\PowerBI;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Filter\Type\PowerBi\CategoryFilterType;
use AppBundle\DataTable\Filter\Type\PowerBi\SubCategoryFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReportDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('title', TextColumnType::class, [
                'label' => 'fields.title',
                'sort' => true,
            ])
            ->addColumn('description', TextColumnType::class, [
                'label' => 'fields.description',
                'sort' => true,
            ])
            ->addColumn('category', TextColumnType::class, [
                'label' => 'fields.category',
                'sort' => true,
            ])
            ->addColumn('subCategories', TemplateColumnType::class, [
                'label' => 'power_bi_report.fields.sub_categories',
                'template_path' => 'power_bi/partials/column/_sub_categories.html.twig',
            ])
            ->addColumn('powerBiUuid', TextColumnType::class, [
                'label' => 'power_bi_report.fields.powerBiUuid',
                'visible' => false,
            ])
        ;

        $builder
            ->addFilter('title', TextFilterType::class, [
                'label' => 'fields.title',
            ])
            ->addFilter('description', TextFilterType::class, [
                'label' => 'fields.description',
            ])
            ->addFilter('category', CategoryFilterType::class, [
                'label' => 'fields.category',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('subCategories', SubCategoryFilterType::class, [
                'label' => 'power_bi_report.fields.sub_categories',
                'form_options' => ['multiple' => true],
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'power_bi_report_show',
            ])
            ->addRowAction('update', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $category): string {
                    return $this->urlGenerator->generate('power_bi_report_edit', [
                        'id' => $category->getIriId(),
                    ]);
                },
                'icon' => 'fa7-solid:edit',
                'visible' => $this->security->isGranted('FEATURE_POWER_BI_REPORT_UPDATE'),
            ])
        ;

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder->setDefaultSortingData(SortingData::fromArray([
            'id' => 'asc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'power_bi_report.title',
        ]);
    }
}
