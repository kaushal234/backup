<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\PowerBi;

use AppBundle\DataTable\Filter\Type\AbstractApiFilterType;
use AppBundle\Form\Type\PowerBI\SubCategoryChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubCategoryFilterType extends AbstractApiFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => SubCategoryChoiceType::class,
                'form_options' => [],
            ])
        ;
    }
}
