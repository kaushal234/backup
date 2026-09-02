<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Service;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommissioningCustomerServiceRecordFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('manufacturerLocation', LocationChoiceType::class, [
                'property_path' => '[equipmentRecord.manufacturerLocation]',
                'label' => 'er.fields.location',
                'required' => false,
            ])
            ->add('salesOrganisation', SSOChoiceType::class, [
                'label' => 'er.fields.sso',
                'property_path' => '[equipmentRecord.salesOrganisation]',
                'required' => false,
            ])
            ->add('salesOrganisationService', SSOChoiceType::class, [
                'label' => 'er.fields.ssoService',
                'property_path' => '[equipmentRecord.salesOrganisationService]',
                'required' => false,
            ])
            ->add('completedAfter', DatePickerType::class, [
                'label' => 'csr.fields.completed_at_after',
                'property_path' => '[completedAt][after]',
                'data' => $options['data']['completedAfter'] ?? (new \DateTime('first day of this month'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
                'empty_data' => (new \DateTime('first day of this month'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
                'required' => false,
            ])
            ->add('completedBefore', DatePickerType::class, [
                'label' => 'csr.fields.completed_at_before',
                'property_path' => '[completedAt][before]',
                'data' => $options['data']['completedBefore'] ?? (new \DateTime('last day of this month'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
                'empty_data' => (new \DateTime('last day of this month'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
                'required' => false,
            ])
            ->add('download', SubmitType::class, [
                'label' => 'menu.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-uppercase'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.filter',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary text-uppercase'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'customer_service_record',
        ]);
    }
}
