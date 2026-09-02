<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Materials\Warehouse;

use ApiBundle\Model\User;
use AppBundle\Form\Type\Directory\Location\WarehouseChoiceType;
use AppBundle\Security\RoleProvider\AclProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TasksMappingType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
        private readonly AclProvider $aclProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $allowedLocationsId = array_unique(preg_filter('/^.*ROLE_(?:WS|MLM)_(\d+)$/', '$1', $this->aclProvider->loadRolesByUser($user)));

        if (null === $data = $builder->getData()) {
            $data = [
                'inboundTasks' => [''],
                'outboundTasks' => [''],
                'administrativeTasks' => [''],
                'excludedTasks' => [''],
            ];

            $builder->setData($data);
        }

        $builder
            ->add('location', WarehouseChoiceType::class, [
                'label' => 'directory.business_unit.fields.location',
                'placeholder' => 'directory.location.make_selection',
                'translation_domain' => 'directory',
                'filters' => ['erpInLN' => true, 'id' => $allowedLocationsId],
            ])
            ->add('inboundTasks', CollectionType::class, [
                'label' => 'materials.tasks_mappings.fields.inbound_tasks',
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'entry_options' => [
                    'attr' => [
                        'placeholder' => 'materials.tasks_mappings.fields.placeholder',
                    ],
                ],
                'required' => false,
                'error_bubbling' => false,
            ])
            ->add('outboundTasks', CollectionType::class, [
                'label' => 'materials.tasks_mappings.fields.outbound_tasks',
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'entry_options' => [
                    'attr' => [
                        'placeholder' => 'materials.tasks_mappings.fields.placeholder',
                    ],
                ],
                'required' => false,
                'error_bubbling' => false,
            ])
            ->add('administrativeTasks', CollectionType::class, [
                'label' => 'materials.tasks_mappings.fields.administrative_tasks',
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'entry_options' => [
                    'attr' => [
                        'placeholder' => 'materials.tasks_mappings.fields.placeholder',
                    ],
                ],
                'required' => false,
                'error_bubbling' => false,
            ])
            ->add('excludedTasks', CollectionType::class, [
                'label' => 'materials.tasks_mappings.fields.excluded_tasks',
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'entry_options' => [
                    'attr' => [
                        'placeholder' => 'materials.tasks_mappings.fields.placeholder',
                    ],
                ],
                'required' => false,
                'error_bubbling' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'materials',
        ]);
    }
}
