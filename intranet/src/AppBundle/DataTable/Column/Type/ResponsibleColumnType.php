<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResponsibleColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'non_conformity.fields.responsible',
                'header_translation_domain' => 'non_conformity',
                'formatter' => static fn (?array $responsible) => $responsible['name'] ?? null,
                'template_path' => 'quality/non_conformity/partial/cell/_responsibles_datatable.html.twig',
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }
}
