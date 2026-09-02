<?php

declare(strict_types=1);

namespace App\DataTable\Column;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractDateTimeColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DateColumnType extends AbstractDateTimeColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('format', 'd-m-Y');
    }
}
