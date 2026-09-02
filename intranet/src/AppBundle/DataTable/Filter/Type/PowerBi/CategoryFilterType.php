<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\PowerBi;

use AppBundle\DataTable\Filter\Type\AbstractApiFilterType;
use AppBundle\Form\Type\PowerBI\CategoryChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryFilterType extends AbstractApiFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => CategoryChoiceType::class,
                'form_options' => [],
            ])
        ;
    }
}
