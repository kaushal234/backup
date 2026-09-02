<?php

declare(strict_types=1);

namespace App\DataTable\Column;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndiceFactorColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'fields.ifactor',
                'header_translation_domain' => 'messages',
                'label_classes' => [
                    'IF 1' => 'success',
                    'IF 10' => 'primary',
                    'IF 100' => 'warning',
                    'IF 1000' => 'danger',
                ],
            ])
        ;
    }

    public function getParent(): ?string
    {
        return LabelColumnType::class;
    }
}
