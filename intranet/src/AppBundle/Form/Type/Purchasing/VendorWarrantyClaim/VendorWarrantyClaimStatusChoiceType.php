<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\VendorWarrantyClaim;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorWarrantyClaimStatusChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label' => 'vendor_warranty_claim.fields.status',
                'translation_domain' => 'vendor_warranty_claim',
                'uri' => 'purchasing/vendor_warranty_claim_statuses',
                'text_key' => '[name]',
                'query' => [
                    'order' => [
                        'position' => 'ASC',
                    ],
                ],
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
