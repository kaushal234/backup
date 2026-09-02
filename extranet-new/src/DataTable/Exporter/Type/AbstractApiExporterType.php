<?php

declare(strict_types=1);

namespace App\DataTable\Exporter\Type;

use App\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Exporter\ExporterInterface;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportFile;
use Kreyu\Bundle\DataTableBundle\Exporter\Type\AbstractExporterType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Delegates the export to the API instead of paginating through the data table client-side,
 * so the exported file is not limited to what the current page (or a re-fetched "all pages")
 * would contain. See {@see ApiProxyQuery::export()}.
 */
abstract class AbstractApiExporterType extends AbstractExporterType
{
    /**
     * @param array<string, mixed> $options
     */
    public function export(DataTableView $view, ExporterInterface $exporter, string $filename, array $options = []): ExportFile
    {
        throw new \LogicException(\sprintf('Do not call export() on "%s", use %s::export() instead.', static::class, ApiProxyQuery::class));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('format')
            ->setAllowedTypes('format', 'string')
            ->setDefaults([
                'extra_query_parameters' => [],
            ])
            ->setAllowedTypes('extra_query_parameters', 'array')
        ;
    }
}
