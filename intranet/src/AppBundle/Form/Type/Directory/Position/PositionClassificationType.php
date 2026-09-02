<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Position;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionClassificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', HiddenType::class)
            ->add('positionCategory', PositionCategoryChoiceType::class, [
                'required' => false,
                'translation_domain' => 'directory',
                'filters' => ['divisions.subDivisions.regions.businessUnits' => $options['businessUnit']],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['businessUnit' => null])
            ->setRequired(['businessUnit'])
        ;
    }
}
