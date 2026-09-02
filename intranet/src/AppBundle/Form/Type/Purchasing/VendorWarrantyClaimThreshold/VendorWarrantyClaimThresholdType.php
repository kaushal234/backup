<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\VendorWarrantyClaimThreshold;

use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class VendorWarrantyClaimThresholdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('threshold', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Range([
                        'min' => 1,
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'edit' === $options['action_type'] ? 'button.save' : 'button.add',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'edit' === $options['action_type'] ? 'btn btn-warning text-capitalize' : 'btn btn-info text-capitalize',
                ],
            ])
        ;

        if ('edit' !== $options['action_type']) {
            $builder->add('location', LocationChoiceType::class, [
                'required' => true,
                'placeholder' => '',
                'currency_in_label' => true,
                'normalizationGroups' => 'currency',
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'vendor_warranty_claim',
            'action_type' => '',
        ]);
    }
}
