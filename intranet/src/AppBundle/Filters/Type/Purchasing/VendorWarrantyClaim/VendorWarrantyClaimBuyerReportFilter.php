<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Purchasing\VendorWarrantyClaim;

use AppBundle\Form\Type\Directory\Location\LocationAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\BuyerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorWarrantyClaimBuyerReportFilter extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', LocationAutocompleteChoiceType::class, [
                'label' => 'fields.location',
                'required' => false,
                'cascading_target_form' => 'buyer',
                'cascading_to_filter' => 'businessUnit.location',
            ])
            ->add('buyer', BuyerAutocompleteChoiceType::class, [
                'label' => 'vendor_warranty_claim.fields.buyer',
                'required' => false,
                'help' => 'vendor_warranty_claim.message.report_buyer_location',
                'translation_domain' => 'vendor_warranty_claim',
            ])
            ->add('buyerSubmit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'csrf_protection' => false,
        ]);
    }
}
