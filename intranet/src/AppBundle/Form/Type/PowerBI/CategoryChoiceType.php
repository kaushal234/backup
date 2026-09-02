<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\PowerBI;

use AppBundle\Form\Type\Common\SelectFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choices' => [
                    'Engineering' => 'ENGINEERING',
                    'Finance' => 'FINANCE',
                    'Manufacturing' => 'MANUFACTURING',
                    'Materials' => 'MATERIALS',
                    'Warehouse' => 'WAREHOUSE',
                ],
            ]);
    }

    public function getParent(): string
    {
        return SelectFormType::class;
    }
}
