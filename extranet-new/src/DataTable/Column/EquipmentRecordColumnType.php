<?php

declare(strict_types=1);

namespace App\DataTable\Column;

use App\Sdk\Resource\EquipmentRecord;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\HtmlColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentRecordColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'toc.fields.model',
                'header_translation_domain' => 'technician_on_call',
                'formatter' => static function (EquipmentRecord $equipmentRecord) {
                    return \sprintf('%s <br/> %s', $equipmentRecord->product, $equipmentRecord->serialNumber);
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return HtmlColumnType::class;
    }
}
