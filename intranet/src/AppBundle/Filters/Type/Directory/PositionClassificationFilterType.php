<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Directory;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PositionClassificationFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('businessUnits', PositionClassificationBusinessUnitChoiceType::class);
    }
}
