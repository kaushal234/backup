<?php

declare(strict_types=1);

namespace App\Twig\Settings;

use App\DataTransferObject\Settings\ChangePassword;
use App\Form\Settings\ChangePasswordType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FormExtension extends AbstractExtension
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('create_settings_password_form', $this->createChangePasswordForm(...)),
        ];
    }

    public function createChangePasswordForm(): FormView
    {
        $url = $this->urlGenerator->generate('settings:change-password');

        $builder = $this->factory->createBuilder(ChangePasswordType::class, new ChangePassword());
        $builder->setAction($url);

        return $builder->getForm()->createView();
    }
}
