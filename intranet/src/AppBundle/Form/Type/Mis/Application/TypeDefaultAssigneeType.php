<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Application;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class TypeDefaultAssigneeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeIri', HiddenType::class)
            ->add('defaultAssignee', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'LKU / GKU' => 'LKU / GKU',
                    'Operational Owner' => 'Operational Owner',
                ],
            ])
        ;
    }
}
