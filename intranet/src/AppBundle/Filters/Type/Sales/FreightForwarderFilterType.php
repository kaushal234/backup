<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FreightForwarderFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'fields.factory',
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'csrf_protection' => false,
            'method' => Request::METHOD_GET,
        ]);
    }
}
