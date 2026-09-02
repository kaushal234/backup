<?php

declare(strict_types=1);

namespace App\Form\Type\Security\PasswordReset;

use App\DataTransferObject\Security\PasswordReset\PasswordResetConfirmation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PasswordResetConfirmationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'security.form.new_password.label',
                    'attr' => ['class' => 'form-control mb-1'],
                ],
                'second_options' => [
                    'label' => 'security.form.confirm_password.label',
                    'attr' => ['class' => 'form-control mb-1'],
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'security',
            'data_class' => PasswordResetConfirmation::class,
        ]);
    }
}
