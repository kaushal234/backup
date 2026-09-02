<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type\Support;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmissionRatingColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'support.equipment_record.fields.emission_rating',
                'header_translation_domain' => 'support',
                'formatter' => static fn (array $product) => $product['name'],
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
