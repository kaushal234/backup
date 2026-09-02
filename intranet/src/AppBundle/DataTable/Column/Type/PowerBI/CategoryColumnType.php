<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type\PowerBI;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'fields.category',
                'header_translation_domain' => 'messages',
                'formatter' => static fn (array $category) => $category['id'],
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
