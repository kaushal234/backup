<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Module\ThirdPartyApp;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClassificationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                'mis.modules.fields.criticity.low' => 0,
                'mis.modules.fields.criticity.high' => 1,
                'mis.modules.fields.criticity.critical' => 2,
            ],
            'translation_domain' => 'mis',
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
