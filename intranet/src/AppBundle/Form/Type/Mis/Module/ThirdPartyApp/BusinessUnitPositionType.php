<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Module\ThirdPartyApp;

use AppBundle\Form\Type\Directory\BusinessUnit\BusinessUnitChoiceType;
use AppBundle\Form\Type\Directory\Position\PositionAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessUnitPositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('businessUnit', BusinessUnitChoiceType::class, [
                'placeholder' => 'directory.business_unit.placeholder',
                'required' => true,
            ])
            ->add('position', PositionAutocompleteChoiceType::class, [
                'placeholder' => 'directory.position.placeholder',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
        ]);
    }
}
