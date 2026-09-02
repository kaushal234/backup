<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IdSearchType extends AbstractType
{
    public const NAME = 'app_id_search';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', TextType::class, [
                'required' => true,
                'label' => $options['id_label'] ?: false,
                'attr' => [
                    'placeholder' => $options['id_placeholder'],
                ],
            ])
            ->add('redirect_route', HiddenType::class, [
                'data' => $options['redirect_route'],
            ])->add('api_route', HiddenType::class, [
                'data' => $options['api_route'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'id_label' => 'by_id',
            'id_placeholder' => '#',
            'redirect_route' => null,
            'api_route' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
