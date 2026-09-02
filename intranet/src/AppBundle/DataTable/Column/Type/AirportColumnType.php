<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AirportColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'fields.airport',
                'header_translation_domain' => 'messages',
                'formatter' => static function (array $airport) {
                    return \sprintf('%s - %s', $airport['code'], $airport['cityName']);
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
