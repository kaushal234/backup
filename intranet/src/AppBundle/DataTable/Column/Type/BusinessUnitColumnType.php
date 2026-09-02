<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessUnitColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.business_unit.name',
                'header_translation_domain' => 'directory',
                'property_path' => '[businessUnit?][name]',
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
