<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class SurveyRatingType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $range = range(0, 5);
        $resolver->setDefaults([
            'required' => true,
            'expanded' => true,
            'choices' => array_combine($range, $range),
            'constraints' => [
                new Range(min: 0, max: 5),
            ],
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
