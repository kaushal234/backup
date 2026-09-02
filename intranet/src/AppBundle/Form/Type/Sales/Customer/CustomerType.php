<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Customer;

use ApiBundle\Form\DataTransformer\SalesRepresentativeTransformer;
use AppBundle\Form\Type\AddressType;
use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\ImageType;
use AppBundle\Form\Type\Purchasing\BusinessPartner\IONBusinessPartnerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CustomerType extends AbstractType
{
    protected $fieldsRestricted = [
        'hidden',
        'status',
    ];

    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $customer = $builder->getData();

        $builder
            ->add('parentCustomer', CustomerAutocompleteChoiceType::class, [
                'label' => 'customers.fields.parent_customer',
                'required' => false,
            ])
            ->add('childrenCustomers', CustomerAutocompleteChoiceType::class, [
                'label' => 'customers.fields.children_customer',
                'required' => false,
                'multiple' => true,
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'hidden' => 0,
                    'normalization_groups_override' => ['customer_list'],
                    'status' => ['APPROVED', 'PENDING RE-APPROVAL'],
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'customers.fields.name',
            ])
            ->add('inforLNBpCode', IONBusinessPartnerAutocompleteChoiceType::class, [
                'label' => 'customers.fields.infor_bp_code',
                'translation_domain' => 'sales_customers',
                'required' => false,
                'query' => [
                    'role' => 'customer',
                ],
            ])
            ->add('address', AddressType::class, [
                'label' => false,
                'country' => false,
            ])
            ->add('country', CountryChoiceType::class, [
                'label' => 'customers.fields.address_country',
                'translation_domain' => 'sales_customers',
            ])
            ->add('phone', TextType::class, [
                'label' => 'customers.fields.phone',
                'required' => false,
            ])
            ->add('fax', TextType::class, [
                'label' => 'customers.fields.fax',
                'required' => false,
            ])
            ->add('hidden', CheckboxType::class, [
                'label' => 'customers.fields.hidden',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'customers.fields.status',
                'choice_translation_domain' => false,
                'choices' => [
                    'PENDING' => 'PENDING',
                    'APPROVED' => 'APPROVED',
                    'NOT APPROVED' => 'NOT APPROVED',
                    'NOT ACTIVE' => 'NOT ACTIVE',
                    'PENDING RE-APPROVAL' => 'PENDING RE-APPROVAL',
                ],
            ])
            ->add('url', UrlType::class, [
                'label' => 'customers.fields.url',
                'required' => false,
            ])
            ->add('customerTypes', CustomerTypeChoiceType::class, [
                'multiple' => true,
            ])
            ->add('mainSalesRepresentative', AsmSubDivisionType::class, [
                'label' => false,
                'required' => false,
                'constraints' => [
                    new Callback(static function ($mainSalesRepresentative, ExecutionContextInterface $context) {
                        if (null !== $mainSalesRepresentative && ('' === $mainSalesRepresentative['asm'] || '' === $mainSalesRepresentative['subDivision'])) {
                            $context
                                ->buildViolation('Both ASM and SubDivision must be set when adding a contact point')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('secondarySalesRepresentatives', CollectionType::class, [
                'entry_type' => AsmSubDivisionType::class,
                'label' => false,
                'entry_options' => [
                    'label' => false,
                    'attr' => ['addLabel' => 'Expend list'],
                ],
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'constraints' => [
                    new Callback(static function ($secondarySalesRepresentatives, ExecutionContextInterface $context) {
                        foreach ($secondarySalesRepresentatives as $secondarySalesRepresentative) {
                            if ('' === $secondarySalesRepresentative['asm'] || '' === $secondarySalesRepresentative['subDivision']) {
                                $context
                                    ->buildViolation('Both ASM and SubDivision must be set when adding a contact point')
                                    ->addViolation();
                            }
                        }
                    }),
                ],
            ])
            ->add('logo', ImageType::class, [
                'label' => 'competitors.fields.logo',
                'translation_domain' => 'sales_competitors',
                'required' => false,
                'mapped' => false,
                'image_url' => isset($customer['logo']) ? $customer['logo']['filePath'] : null,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
            ->add('easymileJiraProjectKey', JiraTracteasyProjectChoiceType::class, [
                'label' => 'customers.fields.easymile_jira_project_key',
                'required' => false,
                'empty_data' => null,
            ])
        ;

        if (!$this->authorizationChecker->isGranted('FEATURE_CUSTOMER_ADMIN') && !$this->authorizationChecker->isGranted('MOO_ECUST')) {
            foreach ($this->fieldsRestricted as $name) {
                $builder->remove($name);
            }
        }

        $builder->get('mainSalesRepresentative')->addModelTransformer(new SalesRepresentativeTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_customers',
        ]);
    }

    public function getName(): string
    {
        return 'app_sales_customer';
    }
}
