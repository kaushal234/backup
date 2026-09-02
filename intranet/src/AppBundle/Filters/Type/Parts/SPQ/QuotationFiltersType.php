<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Parts\SPQ;

use ApiBundle\Client;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\SparePartsHubChoiceType;
use AppBundle\Form\Type\Directory\People\PartsMemberChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuotationFiltersType extends AbstractType
{
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
            ->add('sph', SparePartsHubChoiceType::class, [
                'label' => 'spq.quotations.fields.sph',
                'placeholder' => 'spq.form.make_selection',
                'required' => false,
                'translation_domain' => 'spq',
                'data' => $defaultLocation,
                'empty_data' => $defaultLocation,
            ])
            ->add('quoter', PartsMemberChoiceType::class, [
                'label' => 'spq.quotations.fields.quoter',
                'placeholder' => 'spq.form.make_selection',
                'required' => false,
            ])
            ->add('poster', PartsMemberChoiceType::class, [
                'label' => 'spq.quotations.fields.people.poster',
                'placeholder' => 'spq.form.make_selection',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'spq.quotations.fields.status',
                'placeholder' => 'spq.form.make_selection',
                'choice_translation_domain' => false,
                'choices' => [
                    'PENDING' => 'PENDING',
                    'SUSPENDED' => 'SUSPENDED',
                    'SUBMITTED_PARTIAL' => 'SUBMITTED_PARTIAL',
                    'SUBMITTED_FULL' => 'SUBMITTED_FULL',
                    'SUBMITTED_FULL_REVISED' => 'SUBMITTED_FULL_REVISED',
                    'ORDERED_PARTIAL' => 'ORDERED_PARTIAL',
                    'ORDERED_FULL' => 'ORDERED_FULL',
                    'CANCELLED' => 'CANCELLED',
                    'LOST' => 'LOST',
                ],
                'required' => false,
            ])
            ->add('quotationLines_partNumber', TextType::class, [
                'label' => 'spq.quotations.fields.part_number',
                'property_path' => '[quotationLines.partNumber]',
                'required' => false,
            ])
            ->add('baanCustomerNumber', TextType::class, [
                'label' => 'spq.quotations.fields.cuno',
                'required' => false,
            ])
            ->add('baanSalesOrder', TextType::class, [
                'label' => 'spq.quotations.fields.general.sales_order',
                'required' => false,
            ])
            ->add('customerPurchaseOrder', TextType::class, [
                'label' => 'spq.quotations.fields.customer.purchase_order',
                'required' => false,
            ])
            ->add('rfq', TextType::class, [
                'label' => 'RFQ',
                'translation_domain' => false,
                'required' => false,
            ])
            ->add('submitted_at_after', DatePickerType::class, [
                'property_path' => '[submittedAt][after]',
                'label' => 'spq.form.submitted_at_after',
                'required' => false,
            ])
            ->add('submitted_at_before', DatePickerType::class, [
                'property_path' => '[submittedAt][before]',
                'label' => 'spq.form.submitted_at_before',
                'required' => false,
            ])
            ->add('closed_at_after', DatePickerType::class, [
                'property_path' => '[closedAt][after]',
                'label' => 'spq.form.closed_at_after',
                'required' => false,
            ])
            ->add('closed_at_before', DatePickerType::class, [
                'property_path' => '[closedAt][before]',
                'label' => 'spq.form.closed_at_before',
                'required' => false,
            ])
            ->add('payableService', ChoiceType::class, [
                'label' => 'spq.quotations.fields.payableService',
                'required' => false,
                'placeholder' => '',
                'choices' => ['yes' => 1, 'no' => 0],
                'choice_translation_domain' => 'messages',
            ])
            ->add('download', SubmitType::class, [
                'label' => 'button.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-capitalize'],
            ])
        ;

        if (!\in_array($defaultLocation, $builder->get('sph')->getOption('choices'), true)) {
            $builder->get('sph')->setData(null);
            $builder->get('sph')->setEmptyData(null);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'spq',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_spq_quotations';
    }
}
