<?php

declare(strict_types=1);

namespace App\Form\Security\PasswordReset;

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
                'first_options' => ['label' => 'security.form.new_password.label'],
                'second_options' => ['label' => 'security.form.confirm_password.label'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PasswordResetConfirmation::class,
            'csrf_protection' => true,
            'translation_domain' => 'security',
        ]);
    }
}
