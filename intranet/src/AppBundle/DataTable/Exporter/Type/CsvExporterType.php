<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Exporter\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class CsvExporterType extends AbstractApiExporterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'format' => 'text/csv',
            ])
        ;
    }

    protected function getExtension(): string
    {
        return 'csv';
    }
}
