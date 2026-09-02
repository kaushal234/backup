<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Survey;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RatingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextType::class, [
                'label' => 'general_fields.description',
            ])
            ->add('min', IntegerType::class, [
                'label' => 'rating_type.fields.min',
            ])
            ->add('minLabel', TextType::class, [
                'label' => 'rating_type.fields.minLabel',
            ])
            ->add('max', IntegerType::class, [
                'label' => 'rating_type.fields.max',
            ])
            ->add('maxLabel', TextType::class, [
                'label' => 'rating_type.fields.maxLabel',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'surveys',
            'csrf_protection' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_survey_rating_type';
    }
}
