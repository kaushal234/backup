<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Custom;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemsPerPageType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $keysAndValues = [5, 10, 25, 50, 100];
        $resolver->setDefaults([
            'choices' => array_combine($keysAndValues, $keysAndValues),
            'required' => false,
            'label' => 'itemsPerPage',
            'placeholder' => 'make_selection',
            'translation_domain' => 'messages',
            'choice_translation_domain' => false,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
