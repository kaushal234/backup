<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\NonConformity;

use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NonQualityCostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', LocationChoiceType::class, [
                'erp_in_label' => true,
                'disabled' => true,
                'filters' => ['has_any_capability' => ['factory', 'sso']],
            ])
            ->add('defaultCosts', IntegerType::class)
            ->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-info']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'non_conformity',
        ]);
    }
}
