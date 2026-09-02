<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type\Purchasing\Supplier;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierLocationColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'template_path' => 'purchasing/supplier/partial/cell/_supplier_location.html.twig',
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }
}
