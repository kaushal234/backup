<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use ApiBundle\Model\ApiData;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\ApprovedCustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\ExtranetUser\ExtranetUserAutocompleteChoiceType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaintenanceContractType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ('add' === $options['mode']) {
            $builder->add('buyer', ApprovedCustomerAutocompleteChoiceType::class, [
                'label' => 'service.equipment_record.fields.buyer',
                'required' => true,
                'placeholder' => 'service.maintenance_contract.make_selection',
            ]
            );
            $builder->add('endUser', ApprovedCustomerAutocompleteChoiceType::class, [
                'label' => 'service.equipment_record.fields.end_user',
                'required' => true,
                'placeholder' => 'service.maintenance_contract.make_selection',
            ]
            );
        }

        $builder->add('description', TextareaType::class, [
            'required' => true,
            'label' => 'fields.description',
            'translation_domain' => 'messages',
        ]);
        $builder->add('startDate', DatePickerType::class, [
            'label' => 'service.maintenance_contract.fields.start_date',
            'required' => true,
        ]);
        $builder->add('expirationDate', DatePickerType::class, [
            'label' => 'service.maintenance_contract.fields.expiration_date',
            'required' => true,
        ]);
        $builder->add('representative', PeopleAutocompleteChoiceType::class, [
            'label' => 'directory.location.fields.representative',
            'translation_domain' => 'directory',
            'required' => true,
            'query' => [
                'hidden' => false,
                'disabled' => false,
                'order' => [
                    'lastname' => 'ASC',
                    'firstname' => 'ASC',
                ],
                'normalization_groups_override' => ['people_list'],
                'acls.group.features.name' => 'FEATURE_MAINTENANCE_CONTRACT_WRITE',
            ],
        ]);

        if ('add' === $options['mode']) {
            $builder->add('step1', SubmitType::class, [
                'label' => 'button.next_step',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary text-capitalize'],
            ]);
        }

        $builder->addModelTransformer(new IrisResourceToIdTransformer());

        if ('add' === $options['mode']) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    $this->handleLastStep($form, $data);
                });
        } else {
            /** @var ApiData $data */
            $data = $builder->getData();
            $this->handleLastStep($builder, $data->toArray());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'service',
            'allow_extra_fields' => true,
            'mode' => 'add',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_add_service_maintenance_contract';
    }

    private function handleLastStep($form, array $data)
    {
        if (\is_array($data['buyer'])) {
            $data['buyer'] = $data['buyer']['@id'];
        }
        if (\is_array($data['endUser'])) {
            $data['endUser'] = $data['endUser']['@id'];
        }
        $form->add('step1', HiddenType::class);
        $form->add('buyer', HiddenType::class, ['data' => $data['buyer']]);
        $form->add('endUser', HiddenType::class, ['data' => $data['endUser']]);

        $options = [
            'multiple' => true,
            'required' => true,
        ];
        $buyerRepresentativesOptions = $endUserRepresentativesOptions = $options;

        $buyerRepresentativesOptions['label'] = 'service.maintenance_contract.fields.buyer_representative';
        $buyerRepresentativesOptions['filters']['extranetUserAcls.crt.customer'] = $data['buyer'];
        $endUserRepresentativesOptions['label'] = 'service.maintenance_contract.fields.end_user_representative';
        $endUserRepresentativesOptions['filters']['extranetUserAcls.crt.customer'] = $data['endUser'];

        $form->add('buyerRepresentatives', ExtranetUserAutocompleteChoiceType::class, $buyerRepresentativesOptions);
        $form->add('endUserRepresentatives', ExtranetUserAutocompleteChoiceType::class, $endUserRepresentativesOptions);

        $ersOptions = [
            'label' => 'service.maintenance_contract.fields.equipment_records',
            'multiple' => true,
            'required' => true,
            'query' => [
                'order' => [
                    'serialNumber' => 'ASC',
                    'buyer' => $data['buyer'],
                    'endUser' => $data['endUser'],
                ],
            ],
        ];
        $form->add('equipmentRecords', EquipmentRecordAutocompleteChoiceType::class, $ersOptions);

        $form->add('finish', SubmitType::class, [
            'label' => 'button.submit',
            'translation_domain' => 'messages',
            'attr' => ['class' => 'btn btn-primary text-capitalize'],
        ]);
    }
}
