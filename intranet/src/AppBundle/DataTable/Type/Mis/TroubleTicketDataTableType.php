<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Mis;

use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\PeopleTooltipColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\BooleanFilterType;
use AppBundle\DataTable\Filter\Type\DateRangeFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\DivisionFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\LocationFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\RegionFilterType;
use AppBundle\DataTable\Filter\Type\Directory\BusinessUnit\SubdivisionFilterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\Directory\Premise\PremiseFilterType;
use AppBundle\DataTable\Filter\Type\ExistFilterType;
use AppBundle\DataTable\Filter\Type\Mis\ApplicationFilterType;
use AppBundle\DataTable\Filter\Type\Mis\SupportTeamFilterType;
use AppBundle\DataTable\Filter\Type\Mis\TroubleTicket\SupportLevelFilterType;
use AppBundle\DataTable\Filter\Type\Mis\TroubleTicket\TypeFilterType;
use AppBundle\DataTable\Filter\Type\Module\ModuleFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\LinkColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TroubleTicketDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'mis/trouble_tickets?normalizationGroups[]=subscription';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', IdLinkColumnType::class, [
                'label' => 'display.table.scar_files.headers.id',
                'header_translation_domain' => 'messages',
                'route' => 'trouble_ticket_show',
                'sort' => true,
            ])
            ->addColumn('indiceFactor', LabelColumnType::class, [
                'label' => 'fields.ifactor',
                'header_translation_domain' => 'messages',
                'label_classes' => [
                    'IF 1' => 'default',
                    'IF 10' => 'primary',
                    'IF 100' => 'warning',
                    'IF 1000' => 'danger',
                ],
                'sort' => true,
            ])
            ->addColumn('type', TextColumnType::class, [
                'label' => 'trouble_ticket.fields.type',
                'header_translation_domain' => 'trouble_ticket',
                'property_path' => '[type?][type]',
                'sort' => 'type.type',
            ])
            ->addColumn('typeDescription', TextColumnType::class, [
                'label' => 'trouble_ticket.fields.type_description',
                'header_translation_domain' => 'trouble_ticket',
                'property_path' => '[type?][description]',
                'visible' => false,
                'sort' => 'type.description',
            ])
            ->addColumn('module', LinkColumnType::class, [
                'label' => 'mis.changelog.fields.module',
                'header_translation_domain' => 'mis',
                'href' => function (array $module) {
                    return $this->urlGenerator->generate('mis_modules_show', ['id' => $module['id']]);
                },
                'formatter' => static function (array $module) {
                    return \sprintf('%s - %s', $module['application']['name'], $module['name']);
                },
                'sort' => 'module.name',
            ])
            ->addColumn('shortDescription', TextColumnType::class, [
                'label' => 'trouble_ticket.fields.short_description',
                'header_translation_domain' => 'trouble_ticket',
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'fields.status',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('createdBy', PeopleTooltipColumnType::class, [
                'label' => 'fields.created_by',
                'header_translation_domain' => 'messages',
                'sort' => 'createdBy.lastname',
            ])
            ->addColumn('assignee', PeopleTooltipColumnType::class, [
                'label' => 'tasks.assignee',
                'header_translation_domain' => 'messages',
                'sort' => 'assignee.lastname',
            ])
            ->addColumn('misAssignee', PeopleTooltipColumnType::class, [
                'label' => 'trouble_ticket.fields.mis_assignee',
                'header_translation_domain' => 'trouble_ticket',
                'visible' => false,
                'sort' => 'misAssignee.lastname',
            ])
            ->addColumn('createdAt', DateTimeColumnType::class, [
                'label' => 'fields.created_at',
                'format' => 'Y-m-d',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('lastCommentedAt', DateTimeColumnType::class, [
                'label' => 'trouble_ticket.fields.last_commented_at',
                'header_translation_domain' => 'trouble_ticket',
                'format' => 'Y-m-d H:i',
                'sort' => true,
            ])
            ->addColumn('dueDate', DateTimeColumnType::class, [
                'label' => 'trouble_ticket.fields.due_date',
                'header_translation_domain' => 'trouble_ticket',
                'format' => 'Y-m-d',
                'sort' => true,
                'visible' => false,
            ])
            ->addColumn('jiraIssueNumber', TextColumnType::class, [
                'label' => 'trouble_ticket.fields.jira_number',
                'header_translation_domain' => 'trouble_ticket',
                'visible' => false,
                'sort' => true,
            ])
            ->addColumn('region', TextColumnType::class, [
                'label' => 'menu.region.title',
                'header_translation_domain' => 'messages',
                'property_path' => '[createdBy][businessUnit][region?][name]',
                'sort' => 'createdBy.businessUnit.region.name',
                'visible' => false,
            ])
            ->addColumn('supportLevel', TextColumnType::class, [
                'label' => 'trouble_ticket.fields.support_level',
                'header_translation_domain' => 'trouble_ticket',
                'property_path' => '[supportLevel]',
                'formatter' => static function (?array $supportLevel) {
                    return null === $supportLevel ? '' : \sprintf('Level %d - %s', $supportLevel['level'], $supportLevel['name']);
                },
                'sort' => 'supportLevel.level',
                'visible' => false,
            ])
        ;

        $builder
            ->addFilter('createdBy', PeopleFilterType::class, [
                'label' => 'fields.created_by',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('assignee', PeopleFilterType::class, [
                'label' => 'tasks.assignee',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('location', LocationFilterType::class, [
                'query_path' => 'createdBy.businessUnit.location',
                'label' => 'menu.location.title',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('region', RegionFilterType::class, [
                'label' => 'menu.region.title',
                'translation_domain' => 'messages',
                'query_path' => 'createdBy.businessUnit.region',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('subDivision', SubdivisionFilterType::class, [
                'label' => 'menu.sub_division.title',
                'translation_domain' => 'messages',
                'query_path' => 'createdBy.businessUnit.region.subDivision',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('division', DivisionFilterType::class, [
                'label' => 'menu.division.title',
                'translation_domain' => 'messages',
                'query_path' => 'createdBy.businessUnit.region.subDivision.division',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('misAssignee', PeopleFilterType::class, [
                'label' => 'trouble_ticket.fields.mis_assignee',
                'translation_domain' => 'trouble_ticket',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('operationalOwner', PeopleFilterType::class, [
                'label' => 'mis_application.fields.operational_owner',
                'translation_domain' => 'mis_application',
                'query_path' => 'module.operationalOwner',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('application', ApplicationFilterType::class, [
                'label' => 'trouble_ticket.fields.application',
                'translation_domain' => 'trouble_ticket',
                'query_path' => 'module.application',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('supportTeam', SupportTeamFilterType::class, [
                'label' => 'directory.premise.fields.support_team',
                'translation_domain' => 'directory',
                'query_path' => 'createdBy.premise.supportTeam',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('module', ModuleFilterType::class, [
                'label' => 'menu.modules.title',
                'translation_domain' => 'messages',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('premise', PremiseFilterType::class, [
                'label' => 'directory.premise.title',
                'translation_domain' => 'directory',
                'query_path' => 'createdBy.premise',
            ])
            ->addFilter('jiraIssueNumber', TextFilterType::class, [
                'label' => 'trouble_ticket.fields.jira_number',
                'translation_domain' => 'trouble_ticket',
            ])
            ->addFilter('shortDescription', TextFilterType::class, [
                'label' => 'trouble_ticket.fields.short_description',
                'translation_domain' => 'trouble_ticket',
            ])
            ->addFilter('indiceFactor', TextFilterType::class, [
                'label' => 'fields.ifactor',
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
            ->addFilter('type', TypeFilterType::class, [
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('supportLevel', SupportLevelFilterType::class, [
                'label' => 'trouble_ticket.fields.support_level',
                'translation_domain' => 'trouble_ticket',
                'query_path' => 'supportLevel',
                'form_options' => ['multiple' => true],
            ])
            ->addFilter('status', TextFilterType::class, [
                'form_type' => SelectFormType::class,
                'label' => 'customers.fields.status',
                'translation_domain' => 'sales_customers',
                'form_options' => [
                    'choices' => [
                        'PENDING' => 'PENDING',
                        'PENDING MOO/GKU' => 'PENDING MOO/GKU',
                        'IN PROGRESS' => 'IN PROGRESS',
                        'AWAITING USER' => 'AWAITING USER',
                        'MOO/GKU AWAITING USER' => 'MOO/GKU AWAITING USER',
                        'SOLUTION PROPOSED' => 'SOLUTION PROPOSED',
                        'MOO/GKU SOLUTION PROPOSED' => 'MOO/GKU SOLUTION PROPOSED',
                        'SOLVED' => 'SOLVED',
                        'ALREADY RAISED' => 'ALREADY RAISED',
                        'NOT AN ISSUE' => 'NOT AN ISSUE',
                        'NOT APPROVED' => 'NOT APPROVED',
                    ],
                    'multiple' => true,
                ],
            ])
            ->addFilter('createdAt', DateRangeFilterType::class, [
                'label' => 'fields.created_at',
                'translation_domain' => 'messages',
            ])
            ->addFilter('dueDate', DateRangeFilterType::class, [
                'label' => 'trouble_ticket.fields.due_date',
                'translation_domain' => 'trouble_ticket',
            ])
            ->addFilter('lastCommentedAt', DateRangeFilterType::class, [
                'label' => 'trouble_ticket.fields.last_commented_at',
                'translation_domain' => 'trouble_ticket',
            ])
            ->addFilter('misAssigneeExist', ExistFilterType::class, [
                'label' => 'trouble_ticket.fields.has_mis_assignee',
                'translation_domain' => 'trouble_ticket',
                'query_path' => 'misAssignee',
            ])
            ->addFilter('assigneeExist', ExistFilterType::class, [
                'label' => 'trouble_ticket.fields.has_assignee',
                'translation_domain' => 'trouble_ticket',
                'query_path' => 'assignee',
            ])
            ->addFilter('jiraIssueNumberExist', ExistFilterType::class, [
                'label' => 'trouble_ticket.fields.has_jira_ticket',
                'translation_domain' => 'trouble_ticket',
                'query_path' => 'jiraIssueNumber',
            ])
            ->addFilter('autoEscalated', BooleanFilterType::class, [
                'label' => 'trouble_ticket.fields.auto_escalated',
                'translation_domain' => 'trouble_ticket',
            ])
        ;
        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->setDefaultSortingData(SortingData::fromArray([
                'id' => 'desc',
            ]))
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'trouble_ticket_show',
            ])
            ->addRowAction('toggle_additional_owner', ButtonActionType::class, [
                'label' => '',
                'icon' => function (ApiData $ticket) {
                    /** @var User $user */
                    $user = $this->security->getUser();
                    $additionalOwners = $ticket['additionalOwners'] ?? [];

                    $alreadyOwner = \in_array($user->getIriId(), array_column($additionalOwners, '@id'), true);

                    return $alreadyOwner ? 'fa7-solid:user-minus' : 'fa7-solid:user-plus';
                },
                'href' => function (ApiData $ticket) {
                    /** @var User $user */
                    $user = $this->security->getUser();
                    $additionalOwners = $ticket['additionalOwners'] ?? [];

                    $alreadyOwner = \in_array($user->getIriId(), array_column($additionalOwners, '@id'), true);

                    return $this->urlGenerator->generate(
                        $alreadyOwner ? 'trouble_ticket_remove_additional_owner' : 'trouble_ticket_add_additional_owner',
                        ['id' => $ticket['id']],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                },
            ])
            ->addRowAction('subscription', ButtonActionType::class, [
                'label' => '',
                'icon' => 'fa7-solid:bookmark',
                'visible' => static fn (ApiData $ticket) => false === $ticket['alreadySubscribed'],
                'href' => function (ApiData $ticket) {
                    return $this->urlGenerator->generate('trouble_ticket_follow', [
                        'id' => $ticket['id'],
                    ]);
                },
            ])
        ;
        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });
        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,createdBy,assignee,misAssignee,createdAt,status,module.application.name,module,module.operationalOwner,type,shortDescription,indiceFactor,dueDate,satisfaction,satisfactionComment',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'trouble_ticket.title',
            'translation_domain' => 'trouble_ticket',
        ]);
    }
}
