<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'jobs.fields.country',
                'header_translation_domain' => 'job',
                'formatter' => static fn (array $country) => $country['name'],
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
