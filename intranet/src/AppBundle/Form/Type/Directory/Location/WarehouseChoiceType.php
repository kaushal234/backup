<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->addNormalizer('filters', static fn (Options $options, $value) => array_merge($value, ['capability.warehouse' => 1]));
    }

    public function getBlockPrefix(): string
    {
        return parent::getBlockPrefix().'_warehouse';
    }

    public function getParent(): string
    {
        return LocationChoiceType::class;
    }
}
