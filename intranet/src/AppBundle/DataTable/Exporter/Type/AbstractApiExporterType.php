<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Exporter\Type;

use Kreyu\Bundle\DataTableBundle\DataTableView;
use Kreyu\Bundle\DataTableBundle\Exporter\ExporterInterface;
use Kreyu\Bundle\DataTableBundle\Exporter\ExportFile;
use Kreyu\Bundle\DataTableBundle\Exporter\Type\AbstractExporterType;
use Kreyu\Bundle\DataTableBundle\Exporter\Type\ExporterTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Todo: Need a rework.
 */
abstract class AbstractApiExporterType extends AbstractExporterType
{
    public function export(DataTableView $view, ExporterInterface $exporter, string $filename, array $options = []): ExportFile
    {
        /*
         * Implementing custom exporter is not easy for the moment.
         * ExporterTypeInterface is implementing export method with return value ExportFile.
         * But we're using StreamResponse to send export file from API.
         * Prefer using @see ApiProxyQuery::export()
         */
        throw new \LogicException('Do not call export method of AbstractApiExporterType');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'format' => null,
                'extra_query_parameters' => [],
            ])
            ->setAllowedTypes('format', ['string'])
            ->setAllowedTypes('extra_query_parameters', ['array'])
            ->setRequired('format')
            ->setInfo('format', 'Format used on API resource.')
            ->setInfo('extra_query_parameters', 'Extra query parameters used on API resource.')
        ;
    }
}
