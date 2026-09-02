<?php

declare(strict_types=1);

namespace App\DataTable\Column;

use App\Sdk\Resource\People;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeopleColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.user.name',
                'header_translation_domain' => 'directory',
                'formatter' => static function (People $people) {
                    return \sprintf('%s %s', $people->lastname, $people->firstname);
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
