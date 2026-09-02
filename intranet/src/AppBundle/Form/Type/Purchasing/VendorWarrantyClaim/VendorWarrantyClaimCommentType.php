<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\VendorWarrantyClaim;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorWarrantyClaimCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'translation_domain' => 'demo',
                'label' => 'demo.fields.comment',
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
                'required' => false,
            ])
            ->add('public', HiddenType::class, ['empty_data' => !$options['internal']])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        if (!$options['internal']) {
            $builder->add('recipients', ChoiceType::class, [
                'label' => 'vendor_warranty_claim.fields.supplier_contacts',
                'choices' => $options['contacts'],
                'data' => $options['poster'],
                'property_path' => '[metadata][recipients]',
                'required' => true,
                'multiple' => true,
                'attr' => [
                    'class' => 'dual_select',
                    'size' => '10',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'vendor_warranty_claim',
            'internal' => true,
            'contacts' => [],
            'poster' => null,
        ]);
    }
}
