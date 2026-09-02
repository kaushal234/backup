<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use AppBundle\Twig\Extension\FileSizeExtension;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileSizeColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $fileSizeExtension = new FileSizeExtension();

        $resolver
            ->setDefaults([
                'formatter' => static function (int $value) use ($fileSizeExtension) {
                    return $fileSizeExtension->readableFilesize($value);
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
