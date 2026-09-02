<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilesTooltipColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'news.fields.files',
                'header_translation_domain' => 'news',
                'template_path' => 'bundles/KreyuDataTableBundle/column/picture_tooltip.html.twig',
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }
}
