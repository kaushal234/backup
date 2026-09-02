<?php

declare(strict_types=1);

namespace App\Form\Security\PasswordReset;

use App\DataTransferObject\Security\PasswordReset\PasswordResetRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PasswordResetRequestType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'security.password_reset.form.email.label',
                'required' => true,
                'attr' => ['class' => 'form-control mb-1'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PasswordResetRequest::class,
            'csrf_protection' => true,
            'action' => $this->urlGenerator->generate('security:password-reset:request'),
        ]);
    }
}
