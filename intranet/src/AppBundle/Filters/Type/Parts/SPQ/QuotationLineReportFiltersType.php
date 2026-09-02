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

class QuotationLineReportFiltersType extends AbstractType
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
            ->add('quotation_sph', SparePartsHubChoiceType::class, [
                'property_path' => '[quotation.sph]',
                'label' => 'spq.quotations.fields.sph',
                'placeholder' => 'spq.form.make_selection',
                'required' => false,
                'translation_domain' => 'spq',
                'data' => $defaultLocation,
                'empty_data' => $defaultLocation,
            ])
            ->add('quotation_quoter', PartsMemberChoiceType::class, [
                'property_path' => '[quotation.quoter]',
                'label' => 'spq.quotations.fields.quoter',
                'placeholder' => 'spq.form.make_selection',
                'required' => false,
            ])
            ->add('quotation_poster', PartsMemberChoiceType::class, [
                'property_path' => '[quotation.poster]',
                'label' => 'spq.quotations.fields.people.poster',
                'placeholder' => 'spq.form.make_selection',
                'required' => false,
            ])
            ->add('quotation_status', ChoiceType::class, [
                'property_path' => '[quotation.status]',
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
            ->add('status', ChoiceType::class, [
                'label' => 'spq.quotations.fields.quotation_line.status',
                'placeholder' => 'spq.form.make_selection',
                'choice_translation_domain' => false,
                'choices' => [
                    'PENDING' => 'PENDING',
                    'QUOTED' => 'QUOTED',
                    'SOLD' => 'SOLD',
                    'CANCELLED' => 'CANCELLED',
                    'LOST' => 'LOST',
                    'DELETED' => 'DELETED',
                ],
                'required' => false,
            ])
            ->add('partNumber', TextType::class, [
                'label' => 'spq.quotations.fields.part_number',
                'required' => false,
            ])
            ->add('quotation_baanCustomerNumber', TextType::class, [
                'property_path' => '[quotation.baanCustomerNumber]',
                'label' => 'spq.quotations.fields.cuno',
                'required' => false,
            ])
            ->add('created_at_after', DatePickerType::class, [
                'property_path' => '[createdAt][after]',
                'label' => 'spq.form.created_at_after',
                'required' => false,
            ])
            ->add('created_at_before', DatePickerType::class, [
                'label' => 'spq.form.created_at_before',
                'property_path' => '[createdAt][before]',
                'required' => false,
            ])
            ->add('quotation_closed_at_after', DatePickerType::class, [
                'property_path' => '[quotation.closedAt][after]',
                'label' => 'spq.form.closed_at_after',
                'required' => false,
            ])
            ->add('quotation_closed_at_before', DatePickerType::class, [
                'label' => 'spq.form.closed_at_before',
                'property_path' => '[quotation.closedAt][before]',
                'required' => false,
            ])
            ->add('payableService', ChoiceType::class, [
                'label' => 'spq.quotations.fields.payableService',
                'property_path' => '[quotation.payableService]',
                'required' => false,
                'placeholder' => '',
                'choices' => ['yes' => 1, 'no' => 0],
                'choice_translation_domain' => 'messages',
            ])
            ->add('download', SubmitType::class, [
                'label' => 'button.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-capitalize'],
            ]);

        if (!\in_array($defaultLocation, $builder->get('quotation_sph')->getOption('choices'), true)) {
            $builder->get('quotation_sph')->setData(null);
            $builder->get('quotation_sph')->setEmptyData(null);
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
        return 'app_spq_reports_quotation_lines';
    }
}
