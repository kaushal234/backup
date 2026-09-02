<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PictureColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'template_path' => 'bundles/KreyuDataTableBundle/column/picture.html.twig',
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }
}
