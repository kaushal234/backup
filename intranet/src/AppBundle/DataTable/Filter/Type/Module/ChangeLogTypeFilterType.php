<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Module;

use AppBundle\DataTable\Filter\Handler\ChangeLogTypeFilterHandler;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Type\AbstractFilterType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeLogTypeFilterType extends AbstractFilterType
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder->setHandler(new ChangeLogTypeFilterHandler());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'form_type' => SelectFormType::class,
            'form_options' => [
                'choices' => [
                    '' => '',
                    'Internal' => 'Internal',
                    'New' => 'New',
                    'Fix' => 'Fix',
                    'Performance' => 'Performance',
                    'UI' => 'UI',
                ],
                'multiple' => true,
            ],
        ]);
    }
}
