<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'category',
                'formatter' => static function (?array $category = null): ?string {
                    return $category['name'];
                },
                'property_path' => '[subCategory?][name]',
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
