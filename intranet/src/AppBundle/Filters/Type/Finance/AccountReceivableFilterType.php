<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Finance;

use ApiBundle\Form\DataTransformer\AccountReceivableDueDateTransformer;
use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\EVPPeopleChoiceType;
use AppBundle\Form\Type\Finance\TransactionTypeChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerTypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountReceivableFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerErpReference_customer', CustomerAutocompleteChoiceType::class, [
                'translation_domain' => 'demo',
                'label' => 'demo.fields.customer',
                'required' => false,
                'property_path' => '[customerErpReference.customer]',
            ])
            ->add('customerErpReference_customer_types', CustomerTypeChoiceType::class, [
                'property_path' => '[customerErpReference.customer.customerTypes]',
                'translation_domain' => 'account_receivable',
                'label' => 'account_receivable.fields.customer_type',
                'required' => false,
                'multiple' => true,
            ])
            ->add('country', CountryChoiceType::class, [
                'label' => 'demo.fields.country',
                'translation_domain' => 'demo',
                'required' => false,
            ])
            ->add('customerErpReference_sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
                'property_path' => '[customerErpReference.sso]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('transactionType', TransactionTypeChoiceType::class, [
                'label' => 'account_receivable.fields.transaction_type',
                'property_path' => '[transactionTypeReference.transactionType]',
                'translation_domain' => 'account_receivable',
                'required' => false,
                'multiple' => true,
            ])
            ->add('dueDate', ChoiceType::class, [
                'label' => 'account_receivable.fields.due_date',
                'translation_domain' => 'account_receivable',
                'choice_translation_domain' => false,
                'choices' => [
                    AccountReceivableDueDateTransformer::NOT_PAST_DUE => AccountReceivableDueDateTransformer::NOT_PAST_DUE,
                    AccountReceivableDueDateTransformer::PAST_DUE_30_DAYS => AccountReceivableDueDateTransformer::PAST_DUE_30_DAYS,
                    AccountReceivableDueDateTransformer::PAST_DUE_60_DAYS => AccountReceivableDueDateTransformer::PAST_DUE_60_DAYS,
                    AccountReceivableDueDateTransformer::PAST_DUE_90_DAYS => AccountReceivableDueDateTransformer::PAST_DUE_90_DAYS,
                    AccountReceivableDueDateTransformer::PAST_DUE_180_DAYS => AccountReceivableDueDateTransformer::PAST_DUE_180_DAYS,
                    AccountReceivableDueDateTransformer::PAST_DUE_MORE_THAN_180_DAYS => AccountReceivableDueDateTransformer::PAST_DUE_MORE_THAN_180_DAYS,
                ],
                'required' => false,
            ])
            ->add('customerErpReference_ssd', EVPPeopleChoiceType::class, [
                'label' => 'account_receivable.overview.ssd',
                'translation_domain' => 'account_receivable',
                'property_path' => '[customerErpReference.customer.mainSalesRepresentative.asm.supervisor]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('download', SubmitType::class, [
                'label' => 'menu.download',
                'attr' => [
                    'class' => 'btn btn-danger text-uppercase',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'bottom',
                    'title' => 'menu.download_excel',
                ],
            ])
        ;

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

        $builder->add('customerErpReference_asm', ASMAutocompleteChoiceType::class, $parameters);
        $builder->get('dueDate')->addModelTransformer(new AccountReceivableDueDateTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'messages',
            'method' => Request::METHOD_GET,
            'asmOfSSD' => false,
            'userIri' => null,
        ]);
    }
}
