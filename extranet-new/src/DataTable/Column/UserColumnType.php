<?php

declare(strict_types=1);

namespace App\DataTable\Column;

use App\Sdk\Resource\User;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserColumnType extends AbstractColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'directory.user.name',
                'header_translation_domain' => 'directory',
                'formatter' => static function (User $user) {
                    return \sprintf('%s %s', $user->lastname, $user->firstname);
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return TextColumnType::class;
    }
}
