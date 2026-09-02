<?php

declare(strict_types=1);

namespace App\DataTable\Column;

use App\Sdk\Resource\TechnicianOnCall;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnitOperationalStatusColumnType extends AbstractColumnType
{
    private const COLOR_BY_STATUS = [
        'MCF' => 'success',
        'MCP' => 'warning',
        'NMC' => 'danger',
    ];

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'toc.fields.unit_operational_status_short',
                'header_translation_domain' => 'technician_on_call',
                'getter' => static function (TechnicianOnCall $technicianOnCall): ?string {
                    $status = $technicianOnCall->unitOperationalStatus;

                    return null === $status ? null : \sprintf('%s - %s', $status->name, $status->description);
                },
                'label_classes' => static function (TechnicianOnCall $technicianOnCall): array {
                    $status = $technicianOnCall->unitOperationalStatus;

                    if (null === $status) {
                        return [];
                    }

                    $value = \sprintf('%s - %s', $status->name, $status->description);

                    return [$value => self::COLOR_BY_STATUS[$status->name] ?? 'secondary'];
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return LabelColumnType::class;
    }
}
