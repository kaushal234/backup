<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord;

use ApiBundle\Iri\Iri;
use AppBundle\Controller\Service\CustomerServiceRecord\CustomerServiceRecordController;
use AppBundle\DataPersister\Service\CustomerServiceRecordPersister;
use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\ExtranetUser\ExtranetUserChoiceType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerServiceRecordType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $customerServiceRecord = $builder->getData();

        $builder
            ->add('airport', AirportChoiceType::class, [
                'label' => 'fields.airport',
                'translation_domain' => 'messages',
            ])
            ->add('title', TextType::class, [
                'label' => 'fields.title',
                'translation_domain' => 'messages',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'fields.description',
                'translation_domain' => 'messages',
            ])
        ;

        if (isset($customerServiceRecord['status']) && \in_array($customerServiceRecord['status'], ['PENDING', 'PLANNED', 'ASSIGNED'], true)) {
            $builder
                ->add('plannedAt', DatePickerType::class, [
                    'defaultDate' => new \DateTime(),
                    'label' => 'csr.fields.planned_date',
                    'required' => false,
                ])
                ->add('leader', PeopleAutocompleteChoiceType::class, [
                    'label' => 'csr.fields.technician',
                    'query' => [
                        'hidden' => false,
                        'disabled' => false,
                        'order' => [
                            'lastname' => 'ASC',
                            'firstname' => 'ASC',
                        ],
                        'department' => CustomerServiceRecordController::LEADER_DEPARTMENTS,
                    ],
                    'required' => false,
                    'multiple' => false,
                    'data' => $customerServiceRecord['openIntervention'] ? $customerServiceRecord['openIntervention']['leader'] : null,
                ])
            ;
        }

        if (!\array_key_exists('@id', $customerServiceRecord)) {
            $builder
                ->add('equipmentRecord', EquipmentRecordAutocompleteChoiceType::class, [
                    'label' => 'crab.fields.equipment_record',
                    'translation_domain' => 'crab',
                    'required' => true,
                ])
                ->add('hourmeter', IntegerType::class, [
                    'label' => 'csr.fields.hourmeter',
                    'translation_domain' => 'customer_service_record',
                    'required' => false,
                ])
                ->add('module', ChoiceType::class, [
                    'disabled' => !empty($customerServiceRecord['equipmentRecord']),
                    'label' => 'csr.fields.csr_type',
                    'translation_domain' => 'customer_service_record',
                    'choices' => CustomerServiceRecordPersister::CUSTOMER_SERVICE_RECORD_TYPE,
                ])
                ->add('typeId', IntegerType::class, [
                    'disabled' => !empty($customerServiceRecord['equipmentRecord']),
                    'label' => 'csr.fields.type_number',
                    'translation_domain' => 'customer_service_record',
                    'required' => false,
                ])
            ;

            $builder->addModelTransformer(new CallbackTransformer(
                static function ($array) {
                    if (\array_key_exists('type', $array)) {
                        $array['module'] = $array['type'];
                    }

                    if (\array_key_exists('tocId', $array)) {
                        $array['module'] = 'toc';
                        $array['typeId'] = (int) $array['tocId'];
                    }

                    if (\array_key_exists('serviceBulletinLinesLegacyId', $array)) {
                        $array['module'] = 'sb';
                        $array['typeId'] = $array['serviceBulletinLegacyId'];
                    }

                    return $array;
                },
                static function ($array) {
                    if ('toc' === $array['module']) {
                        $array['tocId'] = (int) $array['typeId'];
                    }

                    if ('sb' === $array['module']) {
                        $array['serviceBulletinLegacyId'] = $array['typeId'];
                    }

                    return $array;
                }
            ));
        }

        if (\array_key_exists('equipmentRecord', $customerServiceRecord)
            && \is_array($customerServiceRecord['equipmentRecord'])
            && \array_key_exists('endUser', $customerServiceRecord['equipmentRecord'])
        ) {
            $builder
                ->add('contact', ExtranetUserChoiceType::class, [
                    'required' => false,
                    'label' => $this->translator->trans('spare_parts_request.fields.contact', [], 'spare_parts_request'),
                    'filters' => [
                        'extranetUserAcls.crt.customer' => [Iri::id($customerServiceRecord['equipmentRecord']['endUser']),
                            Iri::id($customerServiceRecord['equipmentRecord']['buyer']), Iri::id($customerServiceRecord['equipmentRecord']['maintainer'])],
                        'extranetUserAcls.extranetUserGroup.name' => ['role_ST', 'fl_NOT_TOC'],
                        'extranetUserProfile.archived' => 0,
                        'hidden' => 0,
                        'normalization_groups_override' => ['extranet_user_list'],
                        'pagination' => false,
                    ],
                ])
            ;
        }

        if ($customerServiceRecord && \array_key_exists('@id', $customerServiceRecord) && 'CLOSED' === $customerServiceRecord['status']) {
            $availableStatuses = $customerServiceRecord['availableStatus'];
            array_unshift($availableStatuses, $customerServiceRecord['status']);
            $builder
                ->add('status', ChoiceType::class, [
                    'label' => 'Status',
                    'choices' => array_combine($availableStatuses, $availableStatuses),
                ])
            ;
        }

        $builder->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-info']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $translator = $this->translator;

        $resolver->setDefaults([
            'translation_domain' => 'customer_service_record',
            'constraints' => [
                new Callback(static function ($data, ExecutionContextInterface $context) use ($translator) {
                    if (\array_key_exists('@id', $data) || !\array_key_exists('module', $data)) {
                        return;
                    }

                    if ((\array_key_exists('typeId', $data) && null !== $data['typeId']) && \in_array($data['module'], ['default', 'commissioning'], true)) {
                        $context
                            ->buildViolation($translator->trans('csr.errors.no_id_for_default_csr', [], 'customer_service_record'))
                            ->addViolation();
                    }

                    if (\in_array($data['module'], ['toc', 'sb'], true) && null === $data['typeId']) {
                        $context
                            ->buildViolation($translator->trans('csr.errors.id_for_specific_csr', [], 'customer_service_record'))
                            ->addViolation();
                    }
                }),
            ],
        ]);
    }
}
