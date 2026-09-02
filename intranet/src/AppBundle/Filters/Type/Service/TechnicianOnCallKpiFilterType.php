<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Service;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechnicianOnCallKpiFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('salesServiceOrganisation', SSOChoiceType::class, [
                'label' => 'toc.filters.ssoService',
                'translation_domain' => 'technician_on_call',
                'required' => false,
            ])
            ->add('from', DatePickerType::class, [
                'label' => 'toc.filters.from',
                'required' => false,
            ])
            ->add('to', DatePickerType::class, [
                'label' => 'toc.filters.to',
                'required' => false,
            ])
        ;

        if ($options['enabledCustomerFilter']) {
            $builder
                ->add('customers', CustomerAutocompleteChoiceType::class, [
                    'multiple' => true,
                    'required' => false,
                ])
            ;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
            $data = $event->getData() ?? [];

            if (!\is_array($data)) {
                $data = [];
            }

            if (!isset($data['from'])) {
                $data['from'] = (new \DateTime('first day of this month 00:00:00'))->format(DatePickerType::DEFAULT_INPUT_FORMAT);
            }

            if (!isset($data['to'])) {
                $data['to'] = (new \DateTime())->format(DatePickerType::DEFAULT_INPUT_FORMAT);
            }

            $event->setData($data);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData() ?? [];

            if (empty($data['from']) && isset($event->getForm()->getData()['from'])) {
                $data['from'] = (new \DateTime($event->getForm()->getData()['from']))->format('m/d/Y');
            }

            if (empty($data['to']) && isset($event->getForm()->getData()['to'])) {
                $data['to'] = (new \DateTime($event->getForm()->getData()['to']))->format('m/d/Y');
            }

            $event->setData($data);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'technician_on_call',
            'csrf_protection' => false,
            'enabledCustomerFilter' => false,
        ]);
        $resolver->setAllowedTypes('enabledCustomerFilter', 'bool');
    }
}
