<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Sales;

use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Controller\Sales\CustomerController;
use AppBundle\DataTable\Column\Type\DateColumnType;
use AppBundle\DataTable\Column\Type\FileSizeColumnType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\SubdivisionFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CustomerFileDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'sales/customer_files/%s/files';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $tokenManager,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('filePath', TextColumnType::class, [
                'label' => 'files',
                'header_translation_domain' => 'engineering_pictogram',
            ])
            ->addColumn('description', TextColumnType::class, [
                'label' => 'demo.fields.file_description',
                'header_translation_domain' => 'demo',
            ])
            ->addColumn('subDivision', TextColumnType::class, [
                'label' => 'menu.sub_division.title',
                'property_path' => '[subDivision?][name]',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('createdAt', DateColumnType::class, [
                'label' => 'demo.fields.created_at',
                'header_translation_domain' => 'demo',
                'format' => 'Y-m-d H:i:s',
            ])
            ->addColumn('size', FileSizeColumnType::class, [
                'label' => 'size',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('isContract', BooleanColumnType::class, [
                'label' => 'customers.fields.is_contract',
                'header_translation_domain' => 'sales_customers',
            ])
        ;
        $builder
            ->addFilter('subDivision', SubdivisionFilterType::class, [
                'label' => 'menu.sub_division.title',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('description', TextFilterType::class, [
                'label' => 'demo.fields.file_description',
                'translation_domain' => 'demo',
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
        ;

        $builder
            ->addRowAction('download', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $file): string {
                    return $this->urlGenerator->generate('sales_customers_files_show', [
                        'id' => $file->getIriId(),
                        'customerId' => Iri::id($file['customer']),
                    ]);
                },
                'icon' => 'fa7-solid:download',
            ])
            ->addRowAction('viewContract', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $file): string {
                    $contractId = Iri::id($file['contract'] ?? null);

                    if (null === $contractId) {
                        return '#';
                    }

                    return $this->urlGenerator->generate('contract_show', [
                        'id' => $contractId,
                    ]);
                },
                'icon' => 'fa7-solid:file-contract',
                'visible' => static fn (ApiData $file): bool => null !== Iri::id($file['contract'] ?? null),
            ])
            ->addRowAction('edit', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $file): string {
                    return $this->urlGenerator->generate('sales_customers_edit_file', [
                        'id' => $file->getIriId(),
                        'customerId' => Iri::id($file['customer']),
                    ]);
                },
                'icon' => 'fa7-solid:pencil',
                'visible' => $options['canEditCustomerFiles'],
            ])
            ->addRowAction('delete', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $file): string {
                    return $this->urlGenerator->generate('sales_customers_delete_file', [
                        'id' => $file->getIriId(),
                        'customerId' => Iri::id($file['customer']),
                        '_token' => $this->tokenManager->getToken(CustomerController::DELETE_TOKEN_FILE),
                    ]);
                },
                'confirmation' => [
                    'label_title' => 'delete_file.popup.title',
                    'label_description' => 'delete_file.popup.message',
                    'translation_domain' => 'engineering_pictogram',
                ],
                'icon' => 'fa7-solid:trash',
                'variant' => 'danger',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'customers.fields.files',
            'translation_domain' => 'sales_customers',
            'personalization_enabled' => true,
            'canEditCustomerFiles' => false,
        ]);
    }
}
