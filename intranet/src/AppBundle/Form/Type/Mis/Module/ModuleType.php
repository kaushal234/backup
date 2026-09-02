<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Module;

use AppBundle\Form\Type\Directory\Department\DepartmentChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleChoiceType;
use AppBundle\Form\Type\Mis\Application\ApplicationChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ModuleType extends AbstractType
{
    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'mis.modules.fields.name',
            ])
            ->add('frontEndRoute', TextType::class, [
                'label' => 'mis.modules.fields.front_end_route',
                'required' => false,
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'mis.modules.fields.short_desc',
            ])
            ->add('fullDescription', TextareaType::class, [
                'label' => 'mis.modules.fields.full_desc',
                'required' => false,
            ])
            ->add('application', ApplicationChoiceType::class, [
                'label' => 'mis.modules.fields.application',
            ])
            ->add('operationalOwner', PeopleAutocompleteChoiceType::class, [
                'label' => 'mis.modules.fields.moo',
            ])
            ->add('keyUser', PeopleAutocompleteChoiceType::class, [
                'label' => 'mis.modules.fields.key_user',
                'required' => false,
            ])
            ->add('localKeyUsers', PeopleChoiceType::class, [
                'label' => 'mis.modules.fields.lku',
                'placeholder' => 'mis.modules.make_selection',
                'filters' => ['normalization_groups_override' => ['region:list', 'people_list']],
                'required' => false,
                'multiple' => true,
            ])
            ->add('dmsProcedureId', IntegerType::class, [
                'label' => 'mis.modules.fields.dms_procedure',
                'required' => false,
            ])
            ->add('notificationColor', ColorType::class, [
                'label' => 'mis.modules.fields.notification_color',
                'required' => false,
            ])
            ->add('dmsHelpId', IntegerType::class, [
                'label' => 'mis.modules.fields.dms_help',
                'required' => false,
            ])
            ->add('legacyLoc', IntegerType::class, [
                'label' => 'mis.modules.fields.legacy_loc',
                'required' => false,
                'data' => 0,
            ])
            ->add('migrationCurrentStep', MigrationStepChoiceType::class, [
                'label' => 'mis.modules.fields.current_migration_step',
                'required' => false,
            ])
            ->add('migrationEstimatedHours', IntegerType::class, [
                'label' => 'mis.modules.fields.estimated_hours',
                'required' => false,
                'data' => 0,
            ])
            ->add('requiredModules', ModuleChoiceType::class, [
                'label' => 'mis.modules.fields.required_modules',
                'required' => false,
                'multiple' => true,
            ])
            ->add('migrated', CheckboxType::class, [
                'label' => 'mis.modules.fields.migrated',
                'required' => false,
            ])
            ->add('misRelative', CheckboxType::class, [
                'label' => 'mis.modules.fields.mis_relative',
                'required' => false,
            ])
            ->add('department', DepartmentChoiceType::class, [
                'required' => false,
            ])
        ;
        if (true === $options['is_edit']) {
            $builder
                ->add('status', ChoiceType::class, [
                    'label' => 'status',
                    'choices' => [
                        'ACTIVE' => 'ACTIVE',
                        'DISABLED' => 'DISABLED',
                    ],
                    'required' => false,
                ]);
        }

        if (null !== $builder->getData()) {
            $builder
                ->add('transferOperationalOwner', CheckboxType::class, [
                    'label' => 'mis.modules.fields.transfer_moo',
                    'required' => false,
                    'mapped' => false,
                ])
            ;
        }

        if ($this->authorizationChecker->isGranted('FEATURE_MODULES_NOTIFICATION')) {
            if ($options['data']['keyUser']) {
                $builder->add('notifyOperationalOwner', CheckboxType::class, [
                    'label' => 'mis.modules.notify_moo',
                    'required' => false,
                ]);
            }

            if (!empty($options['data']['localKeyUsers'])) {
                $builder->add('notifyKeyUser', CheckboxType::class, [
                    'label' => 'mis.modules.notify_gku',
                    'required' => false,
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'mis',
            'is_edit' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_mis_module';
    }
}
