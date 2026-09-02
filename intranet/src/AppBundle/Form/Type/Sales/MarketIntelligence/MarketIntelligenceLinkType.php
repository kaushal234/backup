<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\MarketIntelligence;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MarketIntelligenceLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marketIntelligence', MarketIntelligenceAutocompleteChoiceType::class, [
                'required' => true,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'mim_link';
    }
}
