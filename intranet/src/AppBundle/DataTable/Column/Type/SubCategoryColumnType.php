<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubCategoryColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'subcategory',
                'formatter' => static function (?array $subCategory = null): ?string {
                    return $subCategory['name'];
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
