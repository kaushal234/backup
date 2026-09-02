<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Engineering\Pictogram;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PictogramType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $pictogram = $builder->getData();

        $builder
            ->add('description', TextType::class, [
                'label' => 'fields.description',
                'required' => true,
                'constraints' => [new Length(min: 3, max: 100), new NotBlank()],
            ])
            ->add('category', CategoryAutocompleteChoiceType::class, [
                'label' => 'category_view',
                'required' => true,
            ])

        ;

        if (null === $pictogram) {
            $builder->add('file', FileType::class, [
                'label' => 'picture',
                'translation_domain' => 'engineering_pictogram',
                'required' => true,
                'help' => 'picture_format',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => 'engineering_pictogram']);
    }
}
