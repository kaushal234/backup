<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Mis\Module;

use ApiBundle\Model\ApiData;
use AppBundle\Controller\Mis\ThirdPartyApp\ExtendedController;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\AbstractSimpleDataTableType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class BusinessUnitPositionDataTableType extends AbstractSimpleDataTableType
{
    public const RESOURCE = 'modules/third_party_app/%s/business_unit_positions';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $tokenManager,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('position', TextColumnType::class, [
                'label' => 'mis.business_unit_position.fields.position',
                'property_path' => '[position][description]',
                'sort' => 'position.description',
            ])
            ->addColumn('businessUnit', TextColumnType::class, [
                'label' => 'mis.business_unit_position.fields.business_unit',
                'property_path' => '[businessUnit][name]',
                'sort' => 'businessUnit.name',
            ])
        ;

        $builder->addRowAction('remove', ButtonActionType::class, [
            'label' => '',
            'href' => function (ApiData $businessUnitPosition): string {
                return $this->urlGenerator->generate('mis_third_party_app_business_unit_position_delete', [
                    'id' => $businessUnitPosition['thirdPartyApp']['id'],
                    'businessUnitPositionId' => $businessUnitPosition->getIriId(),
                    '_token' => $this->tokenManager->getToken(ExtendedController::DELETE_TOKEN),
                ]);
            },
            'confirmation' => [
                'label_title' => 'mis.business_unit_position.delete.popup.title',
                'label_description' => 'mis.business_unit_position.delete.popup.message',
                'translation_domain' => 'mis',
            ],
            'icon' => 'fa7-solid:trash',
            'variant' => 'danger',
        ]);

        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,position.id,position.code,position.description,businessUnit.id,businessUnit.name,businessUnit.region',
                ],
            ])
        ;

        $builder->setDefaultSortingData(SortingData::fromArray([
            'position.description' => 'asc',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'mis.business_unit_position.title',
            'translation_domain' => 'mis',
            'personalization_enabled' => false,
        ]);
    }
}
