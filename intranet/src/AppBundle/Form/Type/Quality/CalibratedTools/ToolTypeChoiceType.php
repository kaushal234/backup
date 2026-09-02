<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\CalibratedTools;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ToolTypeChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'uri' => 'quality/calibrated_tools/tool_types',
                'text_key' => '[description]',
                'query' => [
                    'order' => [
                        'description' => 'ASC',
                    ],
                ],
            ]);
    }

    public function getParent(): string
    {
        return AutocompleteChoiceType::class;
    }
}
