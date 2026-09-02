<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\MarketIntelligence;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarketIntelligenceTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'market_intelligence.fields.type',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'demo.submit',
                'translation_domain' => 'demo',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'market_intelligence',
        ]);
    }
}
