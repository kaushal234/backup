<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Communication;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Column\Type\DateColumnType;
use AppBundle\DataTable\Column\Type\VisibleCheckboxColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContactCampaignDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('__batch', VisibleCheckboxColumnType::class, [
                'enable' => static fn (ApiData $contact) => !$contact['taskId'] || 'CLOSED' === $contact['taskStatus'],
                'visible' => $this->security->isGranted('FEATURE_CREATE_TASK_VERIFIED_EXTRANET_USER'),
            ])
            ->addBatchAction('sendTask', ButtonActionType::class, [
                'label' => 'contact_campaign.fields.create_task',
                'translation_domain' => 'contact_campaign',
                'href' => $this->urlGenerator->generate('contact_campaign_batch_task', ['campaign_id' => $options['campaign_id']]),
                'visible' => $this->security->isGranted('FEATURE_CREATE_TASK_VERIFIED_EXTRANET_USER'),
                'confirmation' => [
                    'label_title' => 'contact_campaign.popup.title',
                    'label_description' => 'contact_campaign.popup.message',
                    'translation_domain' => 'contact_campaign',
                    'type' => 'info',
                ],
            ])
            ->addColumn('contact', TextColumnType::class, [
                'label' => 'contact_campaign.fields.contact',
                'header_translation_domain' => 'contact_campaign',
                'sort' => 'lastname',
                'getter' => static fn ($data) => $data['lastname'].' '.$data['firstname'],
            ])
            ->addColumn('email', TextColumnType::class, [
                'label' => 'contact_campaign.fields.email',
                'header_translation_domain' => 'contact_campaign',
                'sort' => true,
            ])
            ->addColumn('customer', TextColumnType::class, [
                'label' => 'contact_campaign.fields.customer',
                'header_translation_domain' => 'contact_campaign',
                'sort' => 'extranetUserProfile.customer.name',
                'getter' => static fn ($data) => $data['extranetUserProfile']['customer']['name'] ?? '',
            ])
            ->addColumn('address', TextColumnType::class, [
                'label' => 'contact_campaign.fields.address',
                'header_translation_domain' => 'contact_campaign',
                'sort' => false,
                'getter' => static function ($data) {
                    $street1 = $data['address']['street1'] ?? '';
                    $street2 = $data['address']['street2'] ?? '';
                    $postalCode = $data['address']['postalCode'] ?? '';
                    $city = $data['address']['city'] ?? '';
                    $state = $data['address']['state'] ?? '';
                    $country = $data['extranetUserProfile']['country']['name'] ?? '';

                    return \sprintf('%s %s %s %s %s %s', $street1, $street2, $postalCode, $city, $state, $country);
                },
            ])
            ->addColumn('isVerified', TemplateColumnType::class, [
                'label' => 'contact_campaign.fields.is_verified',
                'template_path' => 'communication/contact-campaign/partial/_extranet_user_is_verified.html.twig',
                'getter' => static fn (ApiData|array $contact) => $contact,
            ])
            ->addColumn('closingDateTask', DateColumnType::class, [
                'label' => 'contact_campaign.fields.last_check',
                'header_translation_domain' => 'contact_campaign',
                'getter' => static fn (ApiData $contact) => $contact['taskClosedAt']['date'] ?? null,
                'format' => 'Y-m-d',
            ])
            ->addColumn('task', LinkColumnType::class, [
                'label' => 'contact_campaign.fields.task',
                'header_translation_domain' => 'contact_campaign',
                'sort' => true,
                'getter' => static function (ApiData $contact) {
                    return $contact['taskId'];
                },
                'href' => function (?int $task, ApiData $contact): ?string {
                    return $contact['taskId'] ? $this->urlGenerator->generate('task_show', ['id' => $contact['taskId']]) : null;
                },
            ]);
        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,username,email,extranetUserProfile.customer.name,firstname,lastname,address,extranetUserProfile.country.name,isVerified',
                ],
            ])
        ;

        // Simple search top right, using 'q' parameter of ApiPlatform
        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'contact_campaign.title.contacts',
            'translation_domain' => 'contact_campaign',
            'campaign_id' => null,
        ]);
    }
}
