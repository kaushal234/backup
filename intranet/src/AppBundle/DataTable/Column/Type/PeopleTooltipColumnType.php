<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PeopleTooltipColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.user.name',
                'header_translation_domain' => 'directory',
                'template_path' => 'bundles/KreyuDataTableBundle/column/people_tooltip.html.twig',
                'property_accessor' => PropertyAccess::createPropertyAccessorBuilder()
                    ->disableExceptionOnInvalidPropertyPath()
                    ->getPropertyAccessor(),
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }
}
