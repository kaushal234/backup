<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord;

use AppBundle\Controller\Service\CustomerServiceRecord\CustomerServiceRecordController;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechniciansType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('technicians', TechniciansChoiceType::class, [
                'label' => 'csr.fields.technicians',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('additional', PeopleAutocompleteChoiceType::class, [
                'label' => 'csr.fields.additional_technicians',
                'placeholder' => 'csr.fields.additional_technicians',
                'query' => [
                    'hidden' => false,
                    'disabled' => false,
                    'order' => [
                        'lastname' => 'ASC',
                        'firstname' => 'ASC',
                    ],
                    'normalization_groups_override' => ['people_list'],
                    'department' => CustomerServiceRecordController::LEADER_DEPARTMENTS,
                ],
                'required' => false,
                'multiple' => true,
            ])
        ;

        // Merge value of the two form on the same data key
        $builder->addModelTransformer(new CallbackTransformer(
            static function ($value) use ($builder) {
                if (!$value) {
                    return $value;
                }

                $explodeValue = [];
                foreach ($value as $technician) {
                    if (\in_array($technician, $builder->get('technicians')->getOption('choices'), true)) {
                        $explodeValue['technicians'][] = ['@id' => $technician];
                        continue;
                    }

                    $explodeValue['additional'][] = $technician;
                }

                return $explodeValue;
            },
            static function ($value) {
                $value['interventions.leader'] = array_filter(array_merge($value['technicians'], $value['additional']));
                $value['interventions.leader'] = array_unique($value['interventions.leader']);

                return $value;
            }
        ));

        // If user selected as additional but exist on technicians list, transfert selected value on technicians list
        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) use ($builder) {
            if (!$event->getData() || !\array_key_exists('additional', $event->getData())) {
                return;
            }

            foreach ($event->getData()['additional'] as $key => $technician) {
                if (!\in_array($technician, $builder->get('technicians')->getOption('choices'), true)) {
                    continue;
                }

                $data = $event->getData();
                $data['technicians'][] = $technician;
                unset($data['additional'][$key]);
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'customer_service_record',
        ]);
    }
}
