<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Purchasing;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\DateColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\DepartmentFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Purchasing\VendorWarrantyClaimStatusFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VendorWarrantyClaimsDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.id',
                'header_translation_domain' => 'vendor_warranty_claim',
                'route' => 'vendor_warranty_claim_show',
                'sort' => true,
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.status',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => 'status.name',
                'getter' => static function (ApiData $data) {
                    return $data['status']['name'];
                },
            ])
            ->addColumn('createdAt', DateColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.created_at',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => true,
            ])
            ->addColumn('closedAt', DateColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.closed_at',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => true,
            ])
            ->addColumn('statusUpdatedAt', DateColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.status_updated_at',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => true,
            ])
            ->addColumn('supplierName', TextColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_name',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => true,
            ])
            ->addColumn('supplierNumber', TextColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_number',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => true,
            ])
            ->addColumn('assignee', PeopleColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.assignee',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => 'assignee.lastname',
            ])
            ->addColumn('module', TemplateColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.module',
                'header_translation_domain' => 'vendor_warranty_claim',
                'template_path' => '/purchasing/vendor_warranty_claim/partial/_module_link.html.twig',
                'getter' => static function ($data) {
                    return null;
                },
            ])
            ->addColumn('requestedSupplierAction', TextColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.requested_supplier_action',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => true,
            ])
            ->addColumn('supplierCorrectiveActionRequest', LinkColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.scar',
                'header_translation_domain' => 'vendor_warranty_claim',
                'getter' => static function (ApiData $vendorWarrantyClaim) {
                    return $vendorWarrantyClaim['supplierCorrectiveActionRequest']['id'] ?? null;
                },
                'href' => function (?int $supplierCorrectiveActionRequestId): ?string {
                    return $supplierCorrectiveActionRequestId ? $this->urlGenerator->generate('supplier_corrective_action_request_show', ['id' => $supplierCorrectiveActionRequestId]) : null;
                },
            ])
            ->addColumn('partNumber', LinkColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.part_number',
                'header_translation_domain' => 'vendor_warranty_claim',
                'getter' => static function (ApiData $vendorWarrantyClaim) {
                    $partNumber = '';
                    foreach ($vendorWarrantyClaim['parts'] as $part) {
                        $partNumber .= $part['partNumber'] ?? null;
                    }

                    return $partNumber;
                },
                'href' => function (?string $partNumber): ?string {
                    return $partNumber ? $this->urlGenerator->generate('parts_dashboard_view', ['partNumber' => $partNumber]) : null;
                },
            ])
            ->addColumn('requestedCreditAmount', TextColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.requested_credit_amount',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => true,
            ])
            ->addColumn('supplierCreditAmount', TextColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_credit_amount',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => true,
            ])
            ->addColumn('actualCreditAmount', TextColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.actual_credit_amount',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => true,
            ])
            ->addColumn('currency', TextColumnType::class, [
                'label' => 'vendor_warranty_claim.fields.currency',
                'header_translation_domain' => 'vendor_warranty_claim',
                'sort' => 'currency.name',
                'getter' => static function (ApiData $data): ?string {
                    return $data['currency']['name'] ?? null;
                },
            ])
        ;
        $builder
            ->addFilter('assignee', PeopleFilterType::class)
            ->addFilter('department', DepartmentFilterType::class, [
                'query_path' => 'assignee.department',
            ])
            ->addFilter('location', LocationFilterType::class)
            ->addFilter('status', VendorWarrantyClaimStatusFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('partNumber', TextFilterType::class, [
                'query_path' => 'parts.partNumber',
            ])
            ->addFilter('supplierNumber', TextFilterType::class)
            ->addFilter('supplierName', TextFilterType::class)
            ->addFilter('serialNumber', TextFilterType::class, [
                'query_path' => 'parts.serialNumber',
            ])
            ->addFilter('closedAt', DateRangeFilterType::class)
            ->addFilter('createdAt', DateRangeFilterType::class)
            ->addFilter('poster', PeopleFilterType::class)
            ->addFilter('costPaidBySupplier', BooleanFilterType::class, [
                'query_path' => '[supplierCreditAmount][gt]',
            ])
            ->addFilter('accepted', BooleanFilterType::class)
            ->addFilter('nonConformityId', TextFilterType::class, [
                'query_path' => 'nonConformityId',
            ])
            ->addFilter('warrantyClaimId', TextFilterType::class, [
                'query_path' => 'warrantyClaimId',
            ])
        ;
        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,status.name,createdAt,closedAt,statusUpdateAt,supplierName,supplierNumber,assignee,module,requestedSupplierAction,supplierCorrectiveActionRequest.id,partNumber,requestedCreditAmount,supplierCreditAmount,actualCreditAmount,currency',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'vendor_warranty_claim.title.vwc',
            'translation_domain' => 'vendor_warranty_claim',
        ]);
    }
}
