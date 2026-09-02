<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Demo;

use AppBundle\Form\Type\Common\AirportChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemoAddAirportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('airport', AirportChoiceType::class, [
                'label' => 'fields.airport',
                'translation_domain' => 'messages',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'demo.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'demo',
        ]);
    }
}
