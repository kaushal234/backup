<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Twig;

use Kreyu\Bundle\DataTableBundle\Action\ActionView;
use Kreyu\Bundle\DataTableBundle\Column\ColumnHeaderView;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Filter\FilterView;
use Kreyu\Bundle\DataTableBundle\HeaderRowView;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationView;
use Kreyu\Bundle\DataTableBundle\Twig\DataTableExtension as KreyuDataTableExtension;
use Kreyu\Bundle\DataTableBundle\ValueRowView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DataTableExtension extends AbstractExtension
{
    public function __construct(
        private readonly KreyuDataTableExtension $decoratedDataTableExtension,
    ) {
    }

    public function getFunctions(): array
    {
        $definitions = [
            'data_table' => $this->decoratedDataTableExtension->renderDataTable(...),
            'data_table_table' => $this->decoratedDataTableExtension->renderDataTableTable(...),
            'data_table_action_bar' => $this->decoratedDataTableExtension->renderDataTableActionBar(...),
            'data_table_header_row' => $this->decoratedDataTableExtension->renderHeaderRow(...),
            'data_table_value_row' => $this->decoratedDataTableExtension->renderValueRow(...),
            'data_table_column_label' => $this->decoratedDataTableExtension->renderColumnLabel(...),
            'data_table_column_header' => $this->decoratedDataTableExtension->renderColumnHeader(...),
            'data_table_column_value' => $this->decoratedDataTableExtension->renderColumnValue(...),
            'data_table_action' => $this->decoratedDataTableExtension->renderAction(...),
            'data_table_pagination' => $this->decoratedDataTableExtension->renderPagination(...),
            'data_table_items_per_page' => $this->renderItemsPerPage(...),
            'data_table_filters_form' => $this->decoratedDataTableExtension->renderFiltersForm(...),
            'data_table_personalization_form' => $this->decoratedDataTableExtension->renderPersonalizationForm(...),
            'data_table_export_form' => $this->decoratedDataTableExtension->renderExportForm(...),
        ];

        $functions = [
            new TwigFunction('data_table_filter_clear_url', $this->decoratedDataTableExtension->generateFilterClearUrl(...)),
            new TwigFunction('data_table_column_sort_url', $this->decoratedDataTableExtension->generateColumnSortUrl(...)),
            new TwigFunction('data_table_pagination_url', $this->decoratedDataTableExtension->generatePaginationUrl(...)),
            new TwigFunction('data_table_theme_block', $this->decoratedDataTableExtension->renderThemeBlock(...), [
                'needs_environment' => true,
                'needs_context' => true,
                'is_safe' => ['html'],
            ]),
        ];

        foreach ($definitions as $name => $callable) {
            $functions[] = new TwigFunction($name, $callable, [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]);
        }

        $functions[] = new TwigFunction('data_table_form_aware', $this->decoratedDataTableExtension->renderDataTableFormAware(...), [
            'needs_environment' => true,
            'is_safe' => ['html'],
            'deprecated_info' => true,
        ]);

        return $functions;
    }

    public function getTokenParsers(): array
    {
        return $this->decoratedDataTableExtension->getTokenParsers();
    }

    public function setDataTableThemes(DataTableView $view, array $themes, bool $only = false): void
    {
        $this->decoratedDataTableExtension->setDataTableThemes($view, $themes, $only);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderDataTable(Environment $environment, DataTableView $view, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderDataTable($environment, $view, $variables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderDataTableFormAware(Environment $environment, DataTableView $view, FormView $formView, array $dataTableVariables = [], array $formVariables = []): string
    {
        return $this->decoratedDataTableExtension->renderDataTableFormAware($environment, $view, $formView, $dataTableVariables, $formVariables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderDataTableTable(Environment $environment, DataTableView $view, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderDataTableTable($environment, $view, $variables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderDataTableActionBar(Environment $environment, DataTableView $view, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderDataTableActionBar($environment, $view, $variables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderHeaderRow(Environment $environment, HeaderRowView $view, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderHeaderRow($environment, $view, $variables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderValueRow(Environment $environment, ValueRowView $view, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderValueRow($environment, $view, $variables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderColumnLabel(Environment $environment, ColumnHeaderView $view, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderColumnLabel($environment, $view, $variables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderColumnHeader(Environment $environment, ColumnHeaderView $view, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderColumnHeader($environment, $view, $variables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderColumnValue(Environment $environment, ColumnValueView $view, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderColumnValue($environment, $view, $variables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderAction(Environment $environment, ActionView $view, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderAction($environment, $view, $variables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderPagination(Environment $environment, DataTableView|PaginationView $view, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderPagination($environment, $view, $variables);
    }

    /**
     * @throws RuntimeError
     */
    public function renderItemsPerPage(Environment $environment, DataTableView|PaginationView $view, array $variables = []): string
    {
        if ($view instanceof DataTableView) {
            $view = $view->vars['pagination'];
        }

        if (!empty($themes = $variables['themes'] ?? [])) {
            if (!\is_array($themes)) {
                throw new RuntimeError('The "themes" option passed in the template must be an array.');
            }

            $view->parent->vars['themes'] = $themes;
        }

        return $this->decoratedDataTableExtension->renderThemeBlock(
            environment: $environment,
            context: array_merge($view->vars, $variables),
            dataTable: $view->parent,
            blockName: 'kreyu_data_table_items_per_page',
        );
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderFiltersForm(Environment $environment, FormInterface|FormView $form, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderFiltersForm($environment, $form, $variables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderPersonalizationForm(Environment $environment, FormInterface|FormView $form, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderPersonalizationForm($environment, $form, $variables);
    }

    /**
     * @throws Error
     * @throws \Throwable
     */
    public function renderExportForm(Environment $environment, FormInterface|FormView $form, array $variables = []): string
    {
        return $this->decoratedDataTableExtension->renderExportForm($environment, $form, $variables);
    }

    public function generateFilterClearUrl(DataTableView $dataTableView, FilterView|array $filterViews): string
    {
        return $this->decoratedDataTableExtension->generateFilterClearUrl($dataTableView, $filterViews);
    }

    public function generateColumnSortUrl(DataTableView $dataTableView, ColumnHeaderView|array $columnHeaderViews): string
    {
        return $this->decoratedDataTableExtension->generateFilterClearUrl($dataTableView, $columnHeaderViews);
    }

    public function generatePaginationUrl(DataTableView $dataTableView, int $page): string
    {
        return $this->decoratedDataTableExtension->generatePaginationUrl($dataTableView, $page);
    }
}
