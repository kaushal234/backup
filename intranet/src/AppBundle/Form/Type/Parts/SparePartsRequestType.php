<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\ERPLocationChoiceType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\Location\SparePartsHubChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SparePartsRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sparePartsRequest = $builder->getData();

        $builder
            ->add('activity', TextType::class, [
                'label' => 'spare_parts_request.fields.activity',
                'disabled' => true,
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'spare_parts_request.fields.type',
                'choices' => [
                    'Factory' => 'Factory',
                    'Not Defined Yet' => 'Not Defined Yet',
                    'Payable Services' => 'Payable Services',
                    'Warranty' => 'Warranty',
                    'SSO' => 'SSO',
                ],
            ])
            ->add('customer', TextType::class, [
                'label' => 'demo.fields.customer',
                'translation_domain' => 'demo',
                'disabled' => true,
                'required' => false,
                'data' => $sparePartsRequest['customer']['name'],
            ])
            ->add('airport', TextType::class, [
                'label' => 'fields.airport',
                'translation_domain' => 'messages',
                'disabled' => true,
                'required' => false,
                'data' => null !== $sparePartsRequest['airport'] ? \sprintf('%s - %s', $sparePartsRequest['airport']['code'], $sparePartsRequest['airport']['cityName']) : null,
            ])
            ->add('sph', SparePartsHubChoiceType::class, [
                'label' => 'spare_parts_request.fields.sph',
                'translation_domain' => 'spare_parts_request',
            ])
            ->add('sso', SSOChoiceType::class, [
                'label' => 'directory.department.fields.sso',
                'translation_domain' => 'directory',
            ])
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'directory.department.fields.factory',
                'translation_domain' => 'directory',
            ])
            ->add('erpLocation', ERPLocationChoiceType::class, [
                'label' => 'spare_parts_request.fields.erp_location',
            ])
            ->add('estimatedShippingDate', DatePickerType::class, [
                'label' => 'spare_parts_request.fields.estimated_shipping_date',
                'required' => false,
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'spare_parts_request.fields.notes',
                'required' => false,
                'attr' => [
                    'style' => 'resize:vertical; height:200px',
                ],
            ])
            ->add('deliveryNotes', TextareaType::class, [
                'label' => 'spare_parts_request.fields.delivery_notes',
                'required' => false,
                'attr' => [
                    'style' => 'resize:vertical; height:100px',
                ],
            ])
            ->add('salesOrder', TextType::class, [
                'label' => 'account_receivable.fields.sales_order_number',
                'translation_domain' => 'account_receivable',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'translation_domain' => 'messages',
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'spare_parts_request',
        ]);
    }
}
