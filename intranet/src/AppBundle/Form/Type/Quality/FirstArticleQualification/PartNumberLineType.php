<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\FirstArticleQualification;

use AppBundle\Form\Type\Parts\PartsNumberByErpAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PartNumberLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', PartsNumberByErpAutocompleteChoiceType::class, [
                'required' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('revision', TextType::class, [
                'label' => 'manufacturing.bill_of_materials.headers.revision',
                'translation_domain' => 'messages',
                'required' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('description', TextType::class, [
                'label' => 'manufacturing.bill_of_materials.headers.description',
                'translation_domain' => 'messages',
                'required' => false,
                'constraints' => [new NotBlank()],
            ])
        ;
    }
}
