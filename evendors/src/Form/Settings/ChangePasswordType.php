<?php

declare(strict_types=1);

namespace App\Form\Settings;

use App\DataTransferObject\Settings\ChangePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ChangePasswordType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'settings.change_password.form.new_password.label',
                    'attr' => ['class' => 'form-control mb-1'],
                ],
                'second_options' => [
                    'label' => 'settings.change_password.form.repeat_password.label',
                    'attr' => ['class' => 'form-control mb-1'],
                ],
                'required' => true,
                'label' => 'settings.change_password.form.legend',
                'constraints' => [
                    new Length(
                        null,
                        15,
                        255,
                        null,
                        null,
                        null,
                        null,
                        $this->translator->trans('settings.change_password.form.new_password.short'),
                        $this->translator->trans('settings.change_password.form.new_password.long')
                    ),
                    new NotCompromisedPassword([
                    ], 'settings.change_password.form.new_password.compromised'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChangePassword::class,
            'csrf_protection' => true,
        ]);
    }
}
