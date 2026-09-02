<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Exporter\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class XlsxExporterType extends AbstractApiExporterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'format' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
        ;
    }

    protected function getExtension(): string
    {
        return 'xlsx';
    }
}
