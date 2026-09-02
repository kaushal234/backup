<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactTooltipColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'contacts.extranet_user',
            'header_translation_domain' => 'contacts',
            'template_path' => 'bundles/KreyuDataTableBundle/column/contact_tooltip.html.twig',
        ]);
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }
}
