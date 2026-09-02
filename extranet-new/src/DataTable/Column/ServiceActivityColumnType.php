<?php

declare(strict_types=1);

namespace App\DataTable\Column;

use App\Sdk\Resource\ServiceActivity;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceActivityColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'toc.fields.service_activity',
                'header_translation_domain' => 'technician_on_call',
                'formatter' => static fn (ServiceActivity $serviceActivity) => $serviceActivity->name,
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
