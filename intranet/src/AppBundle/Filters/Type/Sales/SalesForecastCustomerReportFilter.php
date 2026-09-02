<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SalesForecastCustomerReportFilter extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('watchList', CheckboxType::class, [
                'label' => 'customers.fields.watch_list',
                'property_path' => '[options][buyer.watchList]',
                'translation_domain' => 'sales_customers',
                'required' => false,
            ])
            ->add('estimatedSaleDateBefore', TextType::class, [
                'property_path' => '[options][estimatedSaleDate][before]',
                'label' => 'sales_forecasts.reports.estimated_sale_date_before',
                'required' => false,
                'by_reference' => true,
                'attr' => [
                    'class' => 'datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'dd-mm-yyyy',
                ],
            ])
            ->add('estimatedSaleDateAfter', TextType::class, [
                'property_path' => '[options][estimatedSaleDate][after]',
                'label' => 'sales_forecasts.reports.estimated_sale_date_after',
                'required' => false,
                'by_reference' => true,
                'attr' => [
                    'class' => 'datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'dd-mm-yyyy',
                ],
            ])
        ;
    }

    public function getParent(): string
    {
        return SalesForecastMonetizedReportFilter::class;
    }
}
