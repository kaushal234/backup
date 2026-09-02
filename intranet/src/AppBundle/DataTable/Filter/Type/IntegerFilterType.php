<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegerFilterType extends AbstractApiFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => IntegerType::class,
            ])
        ;
    }
}
