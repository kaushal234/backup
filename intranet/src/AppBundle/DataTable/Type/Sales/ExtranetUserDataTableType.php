<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Sales;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\CustomerColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\AirportFilterType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Sales\CustomerFilterType;
use AppBundle\DataTable\Filter\Type\Sales\RoleFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtranetUserDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'sales/extranet_users?normalization_groups[0]=extranet_user_acls';

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => 'contacts.fields.id',
                'header_translation_domain' => 'contacts',
                'route' => 'sales_contact_show',
                'sort' => true,
            ])
            ->addColumn('username', TextColumnType::class, [
                'label' => 'contacts.fields.userid',
                'header_translation_domain' => 'contacts',
            ])
            ->addColumn('name', TextColumnType::class, [
                'label' => 'contacts.fields.name',
                'header_translation_domain' => 'contacts',
                'getter' => static fn (ApiData|array $contact): string => trim(\sprintf('%s %s', $contact['lastname'] ?? '', $contact['firstname'] ?? '')),
            ])
            ->addColumn('department', TextColumnType::class, [
                'label' => 'contacts.fields.department',
                'header_translation_domain' => 'contacts',
                'property_path' => '[extranetUserProfile?][department]',
            ])
            ->addColumn('phone', TextColumnType::class, [
                'label' => 'contacts.fields.phone',
                'header_translation_domain' => 'contacts',
                'getter' => static function (ApiData|array $contact): ?string {
                    $phone = $contact['phones'][0] ?? null;
                    if (!\is_array($phone)) {
                        return null;
                    }

                    $type = $phone['type'] ?? null;
                    $number = $phone['number'] ?? null;

                    if (null === $number || '' === $number) {
                        return null;
                    }

                    return null !== $type && '' !== $type ? \sprintf('%s : %s', $type, $number) : (string) $number;
                },
            ])
            ->addColumn('customer', CustomerColumnType::class, [
                'label' => 'contacts.fields.customer',
                'header_translation_domain' => 'contacts',
                'property_path' => '[extranetUserProfile?][customer?]',
                'sort' => 'extranetUserProfile.customer.name',
            ])
            ->addColumn('extranetUserAcls', TemplateColumnType::class, [
                'label' => 'contacts.menu.crt_roles',
                'header_translation_domain' => 'contacts',
                'template_path' => 'sales/contacts/partial/cells/_xu_role_datatable.html.twig',
                'getter' => static fn (ApiData|array $contact): array => $contact['extranetUserAcls'] ?? [],
            ])
            ->addColumn('disabled', TemplateColumnType::class, [
                'label' => 'contacts.fields.extranet_acess',
                'header_translation_domain' => 'contacts',
                'template_path' => 'sales/contacts/partial/cells/_extranet_access_datatable.html.twig',
            ])
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'sales_contact_show',
            ])
            ->addFilter('customer', CustomerFilterType::class, [
                'label' => 'contacts.fields.customer',
                'translation_domain' => 'contacts',
                'query_path' => 'extranetUserProfile.customer',
            ])
            ->addFilter('airport', AirportFilterType::class, [
                'query_path' => 'extranetUserProfile.airport',
            ])
            ->addFilter('erpLocation', LocationFilterType::class, [
                'label' => 'contacts.fields.erp_location',
                'translation_domain' => 'contacts',
                'query_path' => 'extranetUserProfile.erpLocation',
            ])
            ->addFilter('archived', BooleanFilterType::class, [
                'label' => 'contacts.fields.archived_accounts',
                'translation_domain' => 'contacts',
                'query_path' => 'extranetUserProfile.archived',
            ])
            ->addFilter('role', RoleFilterType::class, [
                'label' => 'contacts.fields.role',
                'translation_domain' => 'contacts',
                'query_path' => 'extranetUserAcls.extranetUserGroup',
                'form_options' => ['multiple' => true],
            ])
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search): void {
                $query->search($search);
            })
            ->setDefaultPaginationData(new PaginationData(
                page: 1,
                perPage: 10,
            ))
            ->setDefaultSortingData(SortingData::fromArray([
                'id' => 'desc',
            ]))
        ;

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,username,lastname,firstname,email,extranetUserProfile.department,phones,extranetUserProfile.customer.name,extranetUserAcls,disabled',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'contacts.last10',
            'translation_domain' => 'contacts',
        ]);
    }
}
