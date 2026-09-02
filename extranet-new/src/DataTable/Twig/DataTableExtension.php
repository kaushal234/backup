<?php

declare(strict_types=1);

namespace App\DataTable\Twig;

use Kreyu\Bundle\DataTableBundle\Action\ActionView;
use Kreyu\Bundle\DataTableBundle\Column\ColumnHeaderView;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Filter\FilterView;
use Kreyu\Bundle\DataTableBundle\HeaderRowView;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationView;
use Kreyu\Bundle\DataTableBundle\Twig\DataTableExtension as KreyuDataTableExtension;
use Kreyu\Bundle\DataTableBundle\ValueRowView;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[AsDecorator(decorates: KreyuDataTableExtension::class)]
class DataTableExtension extends AbstractExtension
{
    public function __construct(
        #[AutowireDecorated]
        private readonly KreyuDataTableExtension $inner,
    ) {
    }

    public function getFunctions(): array
    {
        $definitions = [
            'data_table' => $this->inner->renderDataTable(...),
            'data_table_table' => $this->inner->renderDataTableTable(...),
            'data_table_action_bar' => $this->inner->renderDataTableActionBar(...),
            'data_table_header_row' => $this->inner->renderHeaderRow(...),
            'data_table_value_row' => $this->inner->renderValueRow(...),
            'data_table_column_label' => $this->inner->renderColumnLabel(...),
            'data_table_column_header' => $this->inner->renderColumnHeader(...),
            'data_table_column_value' => $this->inner->renderColumnValue(...),
            'data_table_action' => $this->inner->renderAction(...),
            'data_table_pagination' => $this->inner->renderPagination(...),
            'data_table_items_per_page' => $this->renderItemsPerPage(...),
            'data_table_filters_form' => $this->inner->renderFiltersForm(...),
            'data_table_personalization_form' => $this->inner->renderPersonalizationForm(...),
            'data_table_export_form' => $this->inner->renderExportForm(...),
        ];

        $functions = [
            new TwigFunction('data_table_filter_clear_url', $this->inner->generateFilterClearUrl(...)),
            new TwigFunction('data_table_column_sort_url', $this->inner->generateColumnSortUrl(...)),
            new TwigFunction('data_table_pagination_url', $this->inner->generatePaginationUrl(...)),
        ];

        foreach ($definitions as $name => $callable) {
            $functions[] = new TwigFunction($name, $callable, [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]);
        }

        $functions[] = new TwigFunction('data_table_form_aware', $this->inner->renderDataTableFormAware(...), [
            'needs_environment' => true,
            'is_safe' => ['html'],
            'deprecated_info' => true,
        ]);

        return $functions;
    }

    public function getTokenParsers(): array
    {
        return $this->inner->getTokenParsers();
    }

    /**
     * @param array<string, mixed> $themes
     */
    public function setDataTableThemes(DataTableView $view, array $themes, bool $only = false): void
    {
        $this->inner->setDataTableThemes($view, $themes, $only);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderDataTable(Environment $environment, DataTableView $view, array $variables = []): string
    {
        return $this->inner->renderDataTable($environment, $view, $variables);
    }

    /**
     * @param array<string, mixed> $dataTableVariables
     * @param array<string, mixed> $formVariables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderDataTableFormAware(Environment $environment, DataTableView $view, FormView $formView, array $dataTableVariables = [], array $formVariables = []): string
    {
        return $this->inner->renderDataTableFormAware($environment, $view, $formView, $dataTableVariables, $formVariables);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderDataTableTable(Environment $environment, DataTableView $view, array $variables = []): string
    {
        return $this->inner->renderDataTableTable($environment, $view, $variables);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderDataTableActionBar(Environment $environment, DataTableView $view, array $variables = []): string
    {
        return $this->inner->renderDataTableActionBar($environment, $view, $variables);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderHeaderRow(Environment $environment, HeaderRowView $view, array $variables = []): string
    {
        return $this->inner->renderHeaderRow($environment, $view, $variables);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderValueRow(Environment $environment, ValueRowView $view, array $variables = []): string
    {
        return $this->inner->renderValueRow($environment, $view, $variables);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderColumnLabel(Environment $environment, ColumnHeaderView $view, array $variables = []): string
    {
        return $this->inner->renderColumnLabel($environment, $view, $variables);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderColumnHeader(Environment $environment, ColumnHeaderView $view, array $variables = []): string
    {
        return $this->inner->renderColumnHeader($environment, $view, $variables);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderColumnValue(Environment $environment, ColumnValueView $view, array $variables = []): string
    {
        return $this->inner->renderColumnValue($environment, $view, $variables);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderAction(Environment $environment, ActionView $view, array $variables = []): string
    {
        return $this->inner->renderAction($environment, $view, $variables);
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderPagination(Environment $environment, DataTableView|PaginationView $view, array $variables = []): string
    {
        return $this->inner->renderPagination($environment, $view, $variables);
    }

    /**
     * @param array<string, mixed> $variables
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

        return $this->inner->renderThemeBlock(
            environment: $environment,
            context: array_merge($view->vars, $variables),
            dataTable: $view->parent,
            blockName: 'kreyu_data_table_items_per_page',
        );
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @throws Error
     * @throws \Throwable
     */
    public function renderFiltersForm(Environment $environment, FormInterface|FormView $form, array $variables = []): string
    {
        return $this->inner->renderFiltersForm($environment, $form, $variables);
    }

    /**
     * @param FilterView|array<string, mixed> $filterViews
     */
    public function generateFilterClearUrl(DataTableView $dataTableView, FilterView|array $filterViews): string
    {
        return $this->inner->generateFilterClearUrl($dataTableView, $filterViews);
    }

    /**
     * @param ColumnHeaderView|array<string, mixed> $columnHeaderViews
     */
    public function generateColumnSortUrl(DataTableView $dataTableView, ColumnHeaderView|array $columnHeaderViews): string
    {
        return $this->inner->generateFilterClearUrl($dataTableView, $columnHeaderViews);
    }

    public function generatePaginationUrl(DataTableView $dataTableView, int $page): string
    {
        return $this->inner->generatePaginationUrl($dataTableView, $page);
    }
}
