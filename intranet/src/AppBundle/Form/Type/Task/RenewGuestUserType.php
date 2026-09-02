<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Task;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class RenewGuestUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('renewalDurationMonths', IntegerType::class, [
                'constraints' => [
                    new NotBlank(), new Range(min: 1, max: 12),
                ],
                'label' => 'renew_guest_user.duration_months',
                'required' => false,
            ])
            ->add('renewalYes', SubmitType::class, [
                'label' => 'button.yes',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
            ->add('renewalNo', SubmitType::class, [
                'label' => 'button.no',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-danger'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'task',
        ]);
    }
}
