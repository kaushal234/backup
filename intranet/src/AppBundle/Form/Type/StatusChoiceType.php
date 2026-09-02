<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class StatusChoiceType extends AbstractType
{
    private readonly RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('status', ChoiceType::class, [
            'choice_translation_domain' => false,
            'choices' => array_merge(['' => ''], array_combine($options['choices'], $options['choices'])),
            'attr' => [
                'class' => 'change-status-menu',
            ],
            'choice_attr' => function ($value) use ($options) {
                if ('' === $value) {
                    return [];
                }

                return [
                    'data-route' => $this->router->generate($options['route'], ['id' => $options['id'], 'status' => $value]),
                ];
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [],
            'id' => null,
            'route' => null,
        ]);
    }
}
