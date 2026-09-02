<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[AutoconfigureTag(name: 'kreyu_data_table.filter.type')]
class ChoiceFilterType extends AbstractApiFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'form_type' => ChoiceType::class,
            'form_options' => [
                'placeholder' => '',
            ],
        ]);
    }
}
