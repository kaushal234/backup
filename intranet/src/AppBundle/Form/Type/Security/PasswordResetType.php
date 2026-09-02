<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'security.form.new_password.label',
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length(min: 15, max: 255),
                    ],
                ],
                'second_options' => [
                    'label' => 'security.form.confirm_password.label',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'security',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_security_password_reset';
    }
}
