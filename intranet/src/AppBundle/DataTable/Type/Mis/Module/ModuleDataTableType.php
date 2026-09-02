<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Mis\Module;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\DataTable\Column\Type\DepartmentColumnType;
use AppBundle\DataTable\Column\Type\DmsColumnType;
use AppBundle\DataTable\Column\Type\IdLinkColumnType;
use AppBundle\DataTable\Column\Type\LabelColumnType;
use AppBundle\DataTable\Column\Type\PeopleTooltipColumnType;
use AppBundle\DataTable\Exporter\DefaultExportData;
use AppBundle\DataTable\Exporter\Type\CsvExporterType;
use AppBundle\DataTable\Exporter\Type\XlsxExporterType;
use AppBundle\DataTable\Filter\Type\Directory\People\PeopleFilterType;
use AppBundle\DataTable\Filter\Type\TextFilterType;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\Form\Type\Common\SelectFormType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\NumberColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ModuleDataTableType extends AbstractDataTableType
{
    public const RESOURCE = 'modules';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', IdLinkColumnType::class, [
                'label' => 'fields.name',
                'header_translation_domain' => 'messages',
                'sort' => true,
                'route' => 'mis_modules_show',
            ])
            ->addColumn('shortDescription', TextColumnType::class, [
                'label' => 'fields.short-description',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('type', LabelColumnType::class, [
                'label' => 'mis.modules.fields.type',
                'property_path' => '[@type]',
                'text_values' => [
                    'Module' => 'mis.modules.fields.types.module',
                    'Light' => 'mis.modules.fields.types.light',
                    'Extended' => 'mis.modules.fields.types.extended',
                ],
                'label_classes' => [
                    'Module' => 'default',
                    'Light' => 'success',
                    'Extended' => 'warning',
                ],
            ])
            ->addColumn('application', TextColumnType::class, [
                'label' => 'trouble_ticket.fields.application',
                'header_translation_domain' => 'trouble_ticket',
                'property_path' => '[application?][name]',
                'sort' => 'application.name',
            ])
            ->addColumn('operationalOwner', PeopleTooltipColumnType::class, [
                'label' => 'mis.modules.fields.moo',
                'header_translation_domain' => 'mis',
            ])
            ->addColumn('keyUser', PeopleTooltipColumnType::class, [
                'label' => 'mis.modules.fields.key_user',
                'header_translation_domain' => 'mis',
            ])
            ->addColumn('dmsProcedureId', DmsColumnType::class, [
                'label' => 'mis.modules.fields.dms_procedure',
                'visible' => false,
            ])
            ->addColumn('dmsHelpId', DmsColumnType::class, [
                'label' => 'mis.modules.fields.dms_help',
                'visible' => false,
            ])
            ->addColumn('sso', BooleanColumnType::class, [
                'label' => 'mis.modules.is_sso_activated',
                'label_true' => 'SSO activated',
                'label_false' => 'SSO not activated',
                'visible' => false,
                'getter' => static function (ApiData $module) {
                    return $module['sso'] ?? null;
                },
            ])
            ->addColumn('securityLevel', TextColumnType::class, [
                'label' => 'mis.modules.security_level',
                'property_path' => '[securityLevel?][name]',
                'visible' => false,
                'getter' => static function (ApiData $module) {
                    return $module['securityLevel']['name'] ?? null;
                },
            ])
            ->addColumn('mfaUser', BooleanColumnType::class, [
                'label' => 'mis.modules.mfa_user',
                'label_true' => 'ENABLED',
                'label_false' => 'DISABLED',
                'visible' => false,
                'getter' => static function (ApiData $module) {
                    return $module['mfaUser'] ?? null;
                },
            ])
            ->addColumn('mfaAdmin', BooleanColumnType::class, [
                'label' => 'mis.modules.mfa_admin',
                'label_true' => 'ENABLED',
                'label_false' => 'DISABLED',
                'visible' => false,
                'getter' => static function (ApiData $module) {
                    return $module['mfaAdmin'] ?? null;
                },
            ])
            ->addColumn('passwordPolicyApplied', BooleanColumnType::class, [
                'label' => 'mis.modules.password_policy_applied',
                'label_true' => 'ENABLED',
                'label_false' => 'DISABLED',
                'visible' => false,
                'getter' => static function (ApiData $module) {
                    return $module['passwordPolicyApplied'] ?? null;
                },
            ])
            ->addColumn('lastSecurityReviewOld', TemplateColumnType::class, [
                'label' => 'mis.modules.last_security_review_old',
                'visible' => false,
                'template_path' => 'mis/modules/partial/column/last_review.html.twig',
                'property_path' => 'lastSecurityReviewOld',
                'getter' => static function (ApiData $module) {
                    return $module['lastSecurityReviewOld'] ?? null;
                },
            ])
            ->addColumn('lastAccountReviewOld', TemplateColumnType::class, [
                'label' => 'mis.modules.last_account_review_old',
                'visible' => false,
                'template_path' => 'mis/modules/partial/column/last_review.html.twig',
                'property_path' => 'lastAccountReviewOld',
                'getter' => static function (ApiData $module) {
                    return $module['lastAccountReviewOld'] ?? null;
                },
            ])
            ->addColumn('securityReviewFrequency', TextColumnType::class, [
                'label' => 'mis.modules.security_review_frequency',
                'visible' => false,
                'getter' => static function (ApiData $module) {
                    return $module['securityReviewFrequency'] ?? null;
                },
            ])
            ->addColumn('accountReviewFrequency', TextColumnType::class, [
                'label' => 'mis.modules.account_review_frequency',
                'visible' => false,
                'getter' => static function (ApiData $module) {
                    return $module['accountReviewFrequency'] ?? null;
                },
            ])
            ->addColumn('department', DepartmentColumnType::class, [
                'sort' => 'department.name',
            ])
            ->addColumn('mainAdmin', PeopleTooltipColumnType::class, [
                'label' => 'mis.modules.main_admin',
                'header_translation_domain' => 'mis',
                'visible' => false,
                'getter' => static function (ApiData $module) {
                    return $module['mainAdmin'] ?? null;
                },
            ])
            ->addColumn('countUpdateTasks', NumberColumnType::class, [
                'label' => 'mis.modules.open_updates_tasks',
                'header_translation_domain' => 'mis',
                'sort' => true,
                'getter' => static function ($data) {
                    return $data['countUpdateTasks'] ?? null;
                },
            ])
        ;

        $builder
            ->addRowAction('show', ShowButtonActionType::class, [
                'route' => 'mis_modules_show',
            ])
            ->addRowAction('tts', ButtonActionType::class, [
                'label' => 'TTS',
                'variant' => 'default',
                'href' => function (ApiData $module): string {
                    return $this->urlGenerator->generate('trouble_ticket_home', ['module' => $module->getIriId()]);
                },
            ])
            ->addRowAction('legacyTts', ButtonActionType::class, [
                'label' => 'Legacy TTS',
                'variant' => 'default',
                'href' => function (ApiData $module): string {
                    return $this->urlGenerator->generate('legacy_calendar', [
                        'module' => $module->toArray()['name'],
                        'm' => ['tasks', 'taskList', 'byIntranetModule'],
                    ]);
                },
            ]);

        // Simple search top right, using 'q' parameter of ApiPlatform
        $builder->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
            $query->search($search);
        });
        $builder->addFilter('name', TextFilterType::class);
        $builder->addFilter('shortDescription', TextFilterType::class);
        $builder->addFilter('type', TextFilterType::class, [
            'query_path' => 'resourceType',
            'form_type' => ChoiceType::class,
            'form_options' => [
                'choices' => [
                    'Module' => 'Module',
                    'Third Party App / Light' => 'Light',
                    'Third Party App / Extended' => 'Extended',
                ],
            ],
        ]);
        $builder->addFilter('operationalOwner', PeopleFilterType::class, [
            'label' => 'mis.modules.fields.moo',
            'translation_domain' => 'mis',
        ]);

        $builder->addFilter('withOpenUpdateTasksOnly', TextFilterType::class, [
            'label' => 'mis.modules.with_open_update_task_only',
            'translation_domain' => 'mis',
            'query_path' => 'withOpenUpdateTasksOnly',
            'form_type' => ChoiceType::class,
            'form_options' => [
                'placeholder' => false,
                'choices' => [
                    'No' => '',
                    'Yes' => 'true',
                ],
                'required' => false,
            ],
        ]);

        $builder->addFilter('status', TextFilterType::class, [
            'label' => 'sales_forecasts.fields.status',
            'translation_domain' => 'sales_forecasts',
            'form_type' => SelectFormType::class,
            'form_options' => [
                'choices' => [
                    'ACTIVE' => 'ACTIVE',
                    'DISABLED' => 'DISABLED',
                ],
            ],
        ]);

        $builder->setDefaultExportData(DefaultExportData::fromDefaultArray());
        $builder
            ->addExporter('csv', CsvExporterType::class)
            ->addExporter('xlsx', XlsxExporterType::class, [
                'extra_query_parameters' => [
                    'columns' => 'id,name,operationalOwner,shortDescription,fullDescription,dmsProcedureId,dmsHelpId,legacyLoc,migrationCurrentStep,migrationEstimatedHours,migrated,status,department,keyUser,application,application.jiraProjectId,application.id,misRelative,notifyOperationalOwner,notifyKeyUser,legacyId,lastSecurityReviewOld,lastAccountReviewOld,specification,sso,passwordPolicyApplied,mfaUser,mfaAdmin,securityReviewFrequency,securityReviewDateStart,accountReviewFrequency,accountReviewDateStart,availabilityClassification,integrityClassification,confidentialityClassification,mainAdmin,securityLevel.name,securityLevel.description,securityLevel.id',
                ],
            ])
        ;

        $builder->setDefaultSortingData(SortingData::fromArray([
            'name' => 'asc',
        ]));
        $builder->setDefaultFiltrationData(FiltrationData::fromArray([
            'status' => 'ACTIVE',
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'Modules',
            'translation_domain' => 'mis',
        ]);
    }
}
