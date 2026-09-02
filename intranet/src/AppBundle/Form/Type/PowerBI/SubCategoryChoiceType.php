<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\PowerBI;

use AppBundle\Form\Type\Common\SelectFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubCategoryChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choices' => [
                    'SSO Controlling' => 'SSO CONTROLLING',
                    'Factory Controlling' => 'FACTORY CONTROLLING',
                    'Accounting' => 'ACCOUNTING',
                ],
            ]);
    }

    public function getParent(): string
    {
        return SelectFormType::class;
    }
}
