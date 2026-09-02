<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Support;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManualPartFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('group', ChoiceType::class, [
                'label' => 'support.manual_document_parts.fields.group',
                'placeholder' => 'support.manual_document_parts.fields.make_selection',
                'choice_translation_domain' => false,
                'choices' => [
                    'P' => 'P',
                    'M' => 'M',
                    'O' => 'O',
                    'C' => 'C',
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'support',
        ]);
    }
}
