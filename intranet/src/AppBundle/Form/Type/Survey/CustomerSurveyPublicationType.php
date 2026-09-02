<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Survey;

use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\Directory\Location\ServiceChoiceType;
use AppBundle\Form\Type\Directory\Location\SparePartsHubChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerChoiceType;
use AppBundle\Form\Type\Sales\ExtranetUser\ExtranetUserChoiceType;
use AppBundle\Form\Type\Sales\ExtranetUser\ExtranetUserGroupChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerSurveyPublicationType extends AbstractType
{
    private readonly TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $noResults = $this->translator->trans('no_results', [], 'messages');

        $builder
            ->add('country', CountryChoiceType::class, [
                'label' => 'survey.publication_form.country',
                'required' => false,
                'multiple' => true,
            ])
            ->add('roles', ExtranetUserGroupChoiceType::class, [
                'label' => 'survey.publication_form.roles',
                'required' => false,
                'multiple' => true,
            ])
            ->add('sales_locations', SSOChoiceType::class, [
                'label' => 'survey.publication_form.sales_location',
                'required' => false,
                'multiple' => true,
            ])
            ->add('parts_locations', SparePartsHubChoiceType::class, [
                'label' => 'survey.publication_form.parts_location',
                'required' => false,
                'multiple' => true,
            ])
            ->add('service_locations', ServiceChoiceType::class, [
                'label' => 'survey.publication_form.service_location',
                'required' => false,
                'multiple' => true,
            ])
            ->add('customers', CustomerChoiceType::class, [
                'label' => 'survey.publication_form.customer',
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'class' => 'dual_select',
                    'size' => '20',
                ],
            ])
            ->add('refresh', SubmitType::class, [
                'label' => 'survey.publication_form.refresh',
                'attr' => ['class' => 'btn btn-info btn-block'],
            ])
            ->add('generate', SubmitType::class, [
                'label' => 'survey.publication_form.generate_list',
                'attr' => ['class' => 'btn btn-warning'],
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $this->handleRefresh($form, $data);

                $customers = \array_key_exists('customers', $data) ? $data['customers'] : [];

                if (\count($customers) > 0) {
                    $this->handleGenerate($form, $data);
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'surveys',
            'csrf_protection' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_survey_customer_publication';
    }

    private function handleRefresh(FormInterface $form, array $data)
    {
        $customersFilters = [];

        $form->remove('customers');

        if (isset($data['country']) && null !== $data['country']) {
            $customersFilters['country'] = $data['country'];
        }

        if (isset($data['parts_locations']) && null !== $data['parts_locations']) {
            $customersFilters['crt.partsLocation'] = $data['parts_locations'];
        }

        if (isset($data['service_locations']) && null !== $data['service_locations']) {
            $customersFilters['crt.serviceLocation'] = $data['service_locations'];
        }

        if (isset($data['sales_locations']) && null !== $data['sales_locations']) {
            $customersFilters['crt.erpLocation'] = $data['sales_locations'];
        }

        if (isset($data['roles']) && null !== $data['roles']) {
            $customersFilters['crt.acls.extranetUserGroup'] = $data['roles'];
        }

        $form->add('customers', CustomerChoiceType::class, [
            'label' => 'survey.publication_form.customer',
            'required' => false,
            'multiple' => true,
            'attr' => [
                'size' => '20',
            ],
            'filters' => $customersFilters,
        ]);
    }

    private function handleGenerate(FormInterface $form, array $data)
    {
        $filters = [
            'hidden' => 0,
            'extranetUserAcls.crt.customer' => $data['customers'],
        ];

        if (\array_key_exists('sales_locations', $data)) {
            $filters['extranetUserAcls.crt.erpLocation'] = $data['sales_locations'];
        }

        if (\array_key_exists('parts_locations', $data)) {
            $filters['extranetUserAcls.crt.partsLocation'] = $data['parts_locations'];
        }

        if (\array_key_exists('service_locations', $data)) {
            $filters['extranetUserAcls.crt.serviceLocation'] = $data['service_locations'];
        }

        $form->add('extranet_users', ExtranetUserChoiceType::class, [
            'label' => 'survey.publication_form.extranet_users',
            'required' => false,
            'multiple' => true,
            'attr' => [
                'size' => '40',
            ],
            'name_formatter' => static fn ($extranetUser): string => \sprintf('%s - %s %s, %s', $extranetUser['extranetUserProfile']['customer']['name'] ?: 'UNKNOWN', $extranetUser['lastname'], $extranetUser['firstname'], $extranetUser['username']),
            'filters' => $filters,
        ]);

        $form->add('description', TextType::class, [
            'label' => 'messages.add.campaign_desc',
        ]);

        $form->add('publish', SubmitType::class, [
            'label' => 'messages.add.new_campaign',
            'attr' => ['class' => 'btn btn-primary'],
        ]);
    }
}
