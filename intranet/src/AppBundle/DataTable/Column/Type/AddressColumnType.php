<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'contact_campaign.fields.address',
                'header_translation_domain' => 'contact_campaign',
                'template_path' => 'bundles/KreyuDataTableBundle/column/address.html.twig',
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }
}
