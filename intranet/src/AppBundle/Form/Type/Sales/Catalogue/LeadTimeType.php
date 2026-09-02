<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Catalogue;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LeadTimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productFamily', HiddenType::class)
            ->add('weeks', IntegerType::class, [
                'required' => false,
                'attr' => ['style' => 'max-width: 95px'],
            ])
            ->add('description', TextType::class, [
                'required' => false,
                'attr' => ['style' => 'min-width: 300px'],
            ])
            ->add('reset', CheckboxType::class, ['required' => false, 'attr' => ['class' => 'lead-time-reset']])
        ;
    }
}
