<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Materials\Warehouse;

use ApiBundle\Client;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\WarehouseChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductFamilyChoiceType;
use AppBundle\Form\Type\Sales\Customer\ApprovedCustomerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DashboardFilterType extends AbstractType
{
    final public const OUTBOUND_REQUEST_STATUS_LIST = [
        'CREATED FROM BAAN',
        'DELIVERED PARTIALLY',
        'CLOSED',
    ];
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->client->get('/me');
        $defaultLocation = $user['businessUnit']['location']['@id'] ?? null;

        $builder
            ->add('location', WarehouseChoiceType::class, [
                'label' => 'menu.location.title',
                'placeholder' => 'make_selection',
                'translation_domain' => 'messages',
                'filters' => ['erpInLN' => true],
                'required' => false,
                'data' => $defaultLocation,
            ])
            ->add('baan_operation_starting_date_from', DatePickerType::class, [
                'property_path' => '[baanOperationStartingDate][after]',
                'label' => 'wms.form.operation_starting_date.from',
                'required' => false,
            ])
            ->add('baan_operation_starting_date_until', DatePickerType::class, [
                'property_path' => '[baanOperationStartingDate][before]',
                'label' => 'wms.form.operation_starting_date.until',
                'required' => false,
            ])
            ->add('requested_to_be_delivered_from', DatePickerType::class, [
                'property_path' => '[requestedToBeDeliveredAt][after]',
                'label' => 'wms.form.requested_to_be_delivered_at.from',
                'required' => false,
            ])
            ->add('requested_to_be_delivered_until', DatePickerType::class, [
                'property_path' => '[requestedToBeDeliveredAt][before]',
                'label' => 'wms.form.requested_to_be_delivered_at.until',
                'required' => false,
            ])
            ->add('production_order_greater', TextType::class, [
                'label' => 'shortage_report.fields.orderNumberGte',
                'translation_domain' => 'shortage_report',
                'property_path' => '[productionOrder][gte]',
                'required' => false,
                'attr' => ['placeholder' => '0'],
            ])
            ->add('production_order_lower', TextType::class, [
                'label' => 'shortage_report.fields.orderNumberLte',
                'translation_domain' => 'shortage_report',
                'property_path' => '[productionOrder][lte]',
                'required' => false,
                'attr' => ['placeholder' => '999999'],
            ])
            ->add('project_greater', TextType::class, [
                'label' => 'shortage_report.fields.projectGte',
                'translation_domain' => 'shortage_report',
                'property_path' => '[project][gte]',
                'required' => false,
                'attr' => ['placeholder' => '0'],
            ])
            ->add('project_lower', TextType::class, [
                'label' => 'shortage_report.fields.projectLte',
                'translation_domain' => 'shortage_report',
                'property_path' => '[project][lte]',
                'required' => false,
                'attr' => ['placeholder' => '999999'],
            ])
            ->add('operation_greater', TextType::class, [
                'label' => 'wms.order.operation_from',
                'property_path' => '[operation][gte]',
                'required' => false,
                'attr' => ['placeholder' => '0'],
            ])
            ->add('operation_lower', TextType::class, [
                'label' => 'wms.order.operation_to',
                'property_path' => '[operation][lte]',
                'required' => false,
                'attr' => ['placeholder' => '999'],
            ])
            ->add('slot', TextType::class, [
                'label' => 'manufacturing.planning_online.fields.production_slot',
                'translation_domain' => 'manufacturing',
                'required' => false,
            ])
            ->add('model', ProductFamilyChoiceType::class, [
                'key' => 'name',
                'label' => 'catalogue.family.type',
                'translation_domain' => 'catalogue',
                'required' => false,
            ])
            ->add('customer', ApprovedCustomerAutocompleteChoiceType::class, [
                'label' => 'fields.end_user',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('serialNumber', TextType::class, [
                'label' => 'service.equipment_record.fields.serial_number',
                'translation_domain' => 'service',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'fields.status',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'required' => false,
                'choices' => array_combine(self::OUTBOUND_REQUEST_STATUS_LIST, self::OUTBOUND_REQUEST_STATUS_LIST),
            ])
            ->add('csv', SubmitType::class, [
                'label' => 'button.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-capitalize'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'wms',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'method' => Request::METHOD_GET,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
