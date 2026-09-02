<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use App\DataTable\Filter\EventListener\TransformDateRangeApiFilterData;
use App\DataTable\Filter\Formatter\DateRangeActiveFilterFormatter;
use App\Form\Type\Common\DateRangePickerType;
use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateRangeFilterType extends AbstractApiFilterType
{
    /**
     * @param array{
     *     form_type?: class-string,
     *     active_filter_formatter?: object,
     * } $options
     */
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
