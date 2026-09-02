<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Communication;

use AppBundle\DataTable\Column\Type\DateColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\PeopleColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => '#',
                'sort' => true,
                'route' => 'contact_campaign_show',
            ])
            ->addColumn('name', TextColumnType::class, [
                'label' => 'contact_campaign.fields.name',
                'header_translation_domain' => 'contact_campaign',
                'sort' => true,
            ])
            ->addColumn('startedAt', DateColumnType::class, [
                'label' => 'contact_campaign.fields.started_at',
                'header_translation_domain' => 'contact_campaign',
                'sort' => true,
            ])
            ->addColumn('endedAt', DateColumnType::class, [
                'label' => 'contact_campaign.fields.ended_at',
                'header_translation_domain' => 'contact_campaign',
                'sort' => true,
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'contact_campaign.fields.status',
                'header_translation_domain' => 'contact_campaign',
                'sort' => true,
            ])
            ->addColumn('owner', PeopleColumnType::class, [
                'label' => 'contact_campaign.fields.owner',
                'header_translation_domain' => 'contact_campaign',
                'sort' => 'owner.lastname',
            ])
            ->addColumn('businessUnit', TextColumnType::class, [
                'label' => 'contact_campaign.fields.business_unit',
                'header_translation_domain' => 'contact_campaign',
                'sort' => false,
                'getter' => static function ($data) {
                    $businessUnits = [];
                    foreach ($data['businessUnits'] as $businessUnit) {
                        $businessUnits[] = $businessUnit['name'];
                    }

                    return implode(', ', $businessUnits);
                },
            ])
            ->addColumn('description', TextColumnType::class, [
                'label' => 'contact_campaign.fields.description',
                'header_translation_domain' => 'contact_campaign',
                'sort' => false,
            ]);

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,name,owner,status,startedAt,endedAt,description',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'contact_campaign.title.contact_campaigns',
            'translation_domain' => 'contact_campaign',
        ]);
    }
}
