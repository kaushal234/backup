<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SparePartsRequestStatusType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sparePartsRequest = $builder->getData();

        $builder
            ->add('comment', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'spare_parts_request.status.manually_leave_'.$sparePartsRequest['status'].'_explanation',
                    'style' => 'resize:vertical; height:100px',
                ],
                'mapped' => false,
                'constraints' => 'OPEN' === $sparePartsRequest['status'] ? [new NotBlank()] : [],
                'required' => 'OPEN' === $sparePartsRequest['status'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'spare_parts_request.status.manually_leave_'.$sparePartsRequest['status'],
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        if ('SHIPPED' === $sparePartsRequest['status']) {
            $builder->add('proofOfDelivery', FileType::class, [
                'label' => 'spare_parts_request.fields.proof_of_delivery',
                'translation_domain' => 'spare_parts_request',
                'required' => false,
            ]);
        }
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
