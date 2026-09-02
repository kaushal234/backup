<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type;

use AppBundle\DataTable\Filter\EventListener\TransformDateRangeApiFilterData;
use AppBundle\DataTable\Filter\Formatter\DateRangeActiveFilterFormatter;
use AppBundle\Form\Type\Common\DateRangePickerType;
use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateRangeFilterType extends AbstractApiFilterType
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder
            ->setOperatorSelectable(false)
            ->setDefaultOperator(Operator::Between)
        ;

        $builder
            ->addEventSubscriber(new TransformDateRangeApiFilterData())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => DateRangePickerType::class,
                'active_filter_formatter' => new DateRangeActiveFilterFormatter(),
            ])
        ;
    }
}
