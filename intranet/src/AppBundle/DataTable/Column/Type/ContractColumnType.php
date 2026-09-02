<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'contract',
                'formatter' => static fn (?array $contract) => $contract['name'] ?? null,
            ])
        ;
    }

    public function getParent(): ?string
    {
        return LinkColumnType::class;
    }
}
