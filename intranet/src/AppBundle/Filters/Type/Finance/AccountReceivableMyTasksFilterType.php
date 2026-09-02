<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Finance;

use AppBundle\Form\Type\Directory\People\ASMAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\EVPPeopleChoiceType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountReceivableMyTasksFilterType extends AbstractType
{
    private readonly Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->security->isGranted('ACL_SUPERUSER') || $this->security->isGranted('ACL_ROLE_EVP') || $this->security->isGranted('ACL_ROLE_CFO') || $this->security->isGranted('MOO_AR')) {
            $parameters = [
                'label' => 'customers.fields.asm',
                'translation_domain' => 'sales_customers',
                'property_path' => '[customerErpReference.customer.mainSalesRepresentative.asm]',
                'required' => false,
                'multiple' => true,
            ];

            if ($options['asmOfSSD']) {
                $parameters['query'] = [
                    'supervisor' => $options['userIri'],
                    'hidden' => false,
                    'disabled' => false,
                    'order' => [
                        'lastname' => 'ASC',
                        'firstname' => 'ASC',
                    ],
                    'normalization_groups_override' => ['people_list'],
                    'acls.group.name' => [
                        'ROLE_ASM',
                        'GG_SALES_AGENTS',
                    ],
                ];
            }

            $builder->add('asm', ASMAutocompleteChoiceType::class, $parameters);
        }

        if ($this->security->isGranted('ACL_SUPERUSER') || $this->security->isGranted('ACL_ROLE_CFO') || $this->security->isGranted('MOO_AR')) {
            $builder->add('ssd', EVPPeopleChoiceType::class, [
                'label' => 'account_receivable.overview.ssd',
                'translation_domain' => 'account_receivable',
                'property_path' => '[customerErpReference.customer.mainSalesRepresentative.asm.supervisor]',
                'required' => false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'translation_domain' => 'messages',
            'asmOfSSD' => false,
            'userIri' => null,
            'method' => Request::METHOD_GET,
        ]);
    }
}
