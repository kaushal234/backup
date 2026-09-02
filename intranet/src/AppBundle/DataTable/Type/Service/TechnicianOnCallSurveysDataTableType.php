<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Service;

use AppBundle\DataTable\Column\Type\AirportColumnType;
use AppBundle\DataTable\Column\Type\CustomerColumnType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\LocationColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Column\Type\SimpleLinkColumnType;
use AppBundle\DataTable\Filter\Type\AirportFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationSSOFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\ExtranetUserFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Sales\CustomerFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechnicianOnCallSurveysDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'service/technician_on_call_surveys';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('technicianOnCall', SimpleLinkColumnType::class, [
                'label' => 'toc.card.toc',
                'property_path' => '[technicianOnCall][id]',
                'property_path_link' => '[technicianOnCall][id]',
                'route' => 'technician_on_calls_show',
                'sort' => 'technicianOnCall.id',
            ])
            ->addColumn('execution', TextColumnType::class, [
                'label' => 'toc.fields.survey.execution',
                'header_translation_domain' => 'technician_on_call',
                'sort' => true,
            ])
            ->addColumn('responsiveness', TextColumnType::class, [
                'label' => 'toc.fields.survey.responsiveness',
                'header_translation_domain' => 'technician_on_call',
                'sort' => true,
            ])
            ->addColumn('communication', TextColumnType::class, [
                'label' => 'toc.fields.survey.communication',
                'header_translation_domain' => 'technician_on_call',
                'sort' => true,
            ])
            ->addColumn('attitude', TextColumnType::class, [
                'label' => 'toc.fields.survey.attitude',
                'header_translation_domain' => 'technician_on_call',
                'sort' => true,
            ])
            ->addColumn('comment', TextColumnType::class, [
                'label' => 'fields.comment',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('assignee', PeopleColumnType::class, [
                'label' => 'task.fields.assignee',
                'property_path' => '[technicianOnCall][assignee]',
                'header_translation_domain' => 'task',
                'sort' => 'technicianOnCall.assignee.lastname',
            ])
            ->addColumn('salesOrganisationService', LocationColumnType::class, [
                'label' => 'toc.fields.sales_organisation_service',
                'property_path' => '[technicianOnCall][salesOrganisationService]',
                'sort' => 'technicianOnCall.salesOrganisationService.name',
            ])
            ->addColumn('indiceFactor', LabelColumnType::class, [
                'label' => 'fields.ifactor',
                'property_path' => '[technicianOnCall][indiceFactor]',
                'header_translation_domain' => 'messages',
                'label_classes' => [
                    'IF 1' => 'default',
                    'IF 10' => 'primary',
                    'IF 100' => 'warning',
                    'IF 1000' => 'danger',
                ],
                'sort' => 'technicianOnCall.indiceFactor',
            ])
            ->addColumn('customer', CustomerColumnType::class, [
                'label' => 'fields.customer',
                'property_path' => '[technicianOnCall][customer]',
                'sort' => 'technicianOnCall.customer.name',
            ])
            ->addColumn('mainContact', PeopleColumnType::class, [
                'label' => 'toc.fields.main_contact.label',
                'property_path' => '[technicianOnCall][mainContact]',
                'header_translation_domain' => 'technician_on_call',
            ])
            ->addColumn('airport', AirportColumnType::class, [
                'property_path' => '[technicianOnCall][airport]',
                'sort' => 'technicianOnCall.airport.code',
            ])
            ->addColumn('openDays', TextColumnType::class, [
                'label' => 'toc.fields.open_days',
                'property_path' => '[technicianOnCall][openDays]',
            ])
        ;

        $builder
            ->addFilter('technicianOnCall', TextFilterType::class, [
                'label' => 'toc.filters.id',
                'query_path' => 'technicianOnCall.id',
                'translation_domain' => 'technician_on_call',
            ])
            ->addFilter('assignee', PeopleFilterType::class, [
                'label' => 'tasks.assignee',
                'query_path' => 'technicianOnCall.assignee',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('mainContact', ExtranetUserFilterType::class, [
                'label' => 'toc.fields.main_contact.label',
                'query_path' => 'technicianOnCall.mainContact',
            ])
            ->addFilter('customer', CustomerFilterType::class, [
                'label' => 'toc.fields.customer',
                'query_path' => 'technicianOnCall.customer',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('indiceFactor', TextFilterType::class, [
                'label' => 'fields.ifactor',
                'query_path' => 'technicianOnCall.indiceFactor',
                'translation_domain' => 'messages',
                'form_type' => SelectFormType::class,
                'form_options' => [
                    'choices' => [
                        'IF 1' => 'IF 1',
                        'IF 10' => 'IF 10',
                        'IF 100' => 'IF 100',
                        'IF 1000' => 'IF 1000',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('salesOrganisationService', LocationSSOFilterType::class, [
                'label' => 'toc.filters.ssoService',
                'query_path' => 'technicianOnCall.salesOrganisationService',
                'translation_domain' => 'technician_on_call',
            ])
            ->addFilter('airport', AirportFilterType::class, [
                'label' => 'toc.filters.by_airport',
                'query_path' => 'technicianOnCall.airport',
                'translation_domain' => 'technician_on_call',
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
        ;

        $builder
            ->setDefaultSortingData(SortingData::fromArray([
                'id' => 'desc',
            ]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'toc.title.survey.title_list',
            'translation_domain' => 'technician_on_call',
        ]);
    }
}
