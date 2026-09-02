<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Order;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMChoiceType;
use AppBundle\Form\Type\ResourceChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerChoiceType;
use AppBundle\Form\Type\Sales\ExtranetUser\ExtranetUserAutocompleteChoiceType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class OrderType extends AbstractType
{
    /** @var DataProvider */
    protected $dataProvider;
    private readonly AuthorizationCheckerInterface $authorizationChecker;
    private $client;

    public function __construct(DataProvider $dataProvider, AuthorizationCheckerInterface $authorizationChecker, Client $client)
    {
        $this->dataProvider = $dataProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->client = $client;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'placeholder' => 'make_selection',
            ]);

        if (!$options['juridical_location_only'] && !$options['baan_customer_number_only']) {
            $builder
                ->add('equoteId', TextType::class, [
                    'label' => 'sales_forecasts.fields.equote',
                    'translation_domain' => 'sales_forecasts',
                    'required' => false,
                ])
                ->add('asm', ASMChoiceType::class, [
                    'label' => 'customers.fields.asm',
                    'translation_domain' => 'sales_customers',
                    'choice_translation_domain' => false,
                    'placeholder' => 'customers.make_selection',
                ])
                ->add('buyer', CustomerAutocompleteChoiceType::class, [
                    'label' => 'fields.buyer',
                    'translation_domain' => 'messages',
                    'required' => false,
                    'cascading_target_form' => 'contact',
                    'cascading_to_filter' => 'extranetUserAcls.crt.customer',
                    'query' => [
                        'order' => [
                            'name' => 'ASC',
                        ],
                        'active' => true,
                    ],
                ])
                ->add('contact', ExtranetUserAutocompleteChoiceType::class, [
                    'label' => 'fields.contact',
                    'translation_domain' => 'messages',
                    'required' => false,
                ])
                ->add('endUser', CustomerChoiceType::class, [
                    'label' => 'fields.end_user',
                    'translation_domain' => 'messages',
                    'choice_translation_domain' => false,
                    'placeholder' => 'make_selection',
                ])
                ->add('newCustomer', ChoiceType::class, [
                    'label' => 'sales_order.fields.new_customer',
                    'choices' => ['Yes' => true, 'No' => false],
                    'choice_translation_domain' => false,
                    'placeholder' => 'sales_order.make_selection',
                ])
                ->add('salesAgent', CustomerChoiceType::class, [
                    'label' => 'sales_order.fields.sales_agent',
                    'choice_translation_domain' => false,
                    'placeholder' => 'sales_order.make_selection',
                    'required' => false,
                ])
                ->add('customerPurchaseOrders', CollectionType::class, [
                    'label' => 'sales_order.fields.customer_purchase_orders',
                    'entry_type' => TextType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'required' => false,
                    'error_bubbling' => false,
                ])
                ->add('baanOrderNumbers', CollectionType::class, [
                    'label' => 'sales_order.fields.baan_order_numbers',
                    'entry_type' => TextType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'entry_options' => [
                        'attr' => [
                            'placeholder' => 'XXXXXX',
                        ],
                    ],
                    'required' => false,
                    'error_bubbling' => false,
                ])
            ;

            if ($this->authorizationChecker->isGranted('FEATURE_SALES_ORDER_EDIT')) {
                $builder->add('note', TextareaType::class, ['required' => false]);
            }
        }

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $this->setupBusinessPartnerCodeField($form);
            }
        );

        if ($builder->has('buyer')) {
            $builder->get('buyer')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    $this->setupBusinessPartnerCodeField($form->getParent());
                }
            );
        }

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                if (null === ($order = $event->getData()) || !$order instanceof ApiData) {
                    $this->setupJuridicalLocationField($form);

                    return;
                }

                if (!$options['baan_customer_number_only']) {
                    $this->setupJuridicalLocationField($form, isset($order['sso']) ? $order['sso']['@id'] : null);
                }
            }
        );

        $builder->get('sso')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                if (!$options['baan_customer_number_only']) {
                    $this->setupJuridicalLocationField($form->getParent(), $form->getData());
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_orders',
            'juridical_location_only' => false,
            'baan_customer_number_only' => false,
        ]);
    }

    private function setupBusinessPartnerCodeField(FormInterface $form): void
    {
        if (!$form->has('buyer')) {
            return;
        }

        $buyerFormData = $form->get('buyer')->getData();
        $customer = match (\gettype($buyerFormData)) {
            'string' => $this->client->get($buyerFormData),
            'array' => \array_key_exists('@id', $buyerFormData) ? $this->client->get($buyerFormData['@id']) : null,
            default => null,
        };

        $businessPartnerCodes = $customer ? array_merge($customer['inforLnBusinessPartnerCodes'] ?? [], ['N/A']) : [];

        $form->add('inforLnBusinessPartnerCode', ChoiceType::class, [
            'label' => 'sales_order.fields.infor_bp_code',
            'translation_domain' => 'sales_orders',
            'placeholder' => 'sales_order.fields.placeholders.select_bp_code',
            'choices' => array_combine($businessPartnerCodes, $businessPartnerCodes),
        ]);
    }

    private function setupJuridicalLocationField(FormInterface $form, $ssoIri = null)
    {
        switch ($ssoIri) {
            case '/locations/36': // TLD LAC - ERP 310
                $filters = ['id' => [3, 11]]; // TLD America Inc., TLD Japan Co., Ltd
                break;
            case '/locations/1': // TLD ASI - ERP 600
                $filters = ['id' => [5, 9, 11]]; // TLD Asia Ltd, TLD Asia (Singapore) Pte Ltd., TLD Japan Co., Ltd
                break;
            default:
                $filters = empty($ssoIri) ? [] : ['locations' => $ssoIri];
        }

        if (!$filters) {
            $form->remove('juridicalLocation');

            return;
        }
        $choices = [];

        foreach ($this->dataProvider->findAll('juridical_locations', $filters, ['name']) as $juridicalLocation) {
            $choices[$juridicalLocation['name']] = $juridicalLocation['@id'];
        }
        if ([] === $choices) {
            $form->remove('juridicalLocation');

            return;
        }

        $options = [
            'label' => 'directory.juridical_location.name',
            'translation_domain' => 'directory',
            'choice_translation_domain' => false,
            'placeholder' => 'directory.people.make_selection',
            'choices' => $choices,
        ];

        if (1 === \count($choices)) {
            $options['data'] = current($choices);
        }

        $form->add('juridicalLocation', ResourceChoiceType::class, $options);
    }
}
