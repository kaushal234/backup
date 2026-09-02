<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CurrencyColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.location.fields.currency',
                'header_translation_domain' => 'directory',
                'formatter' => static fn (array $currency) => $currency['name'],
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
