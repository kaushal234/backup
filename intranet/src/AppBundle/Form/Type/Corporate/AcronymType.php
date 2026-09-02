<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Corporate;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AcronymType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('acronym', TextType::class, [
                'label' => 'corporate.acronyms.acronym',
                'required' => !$options['lax'],
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'corporate.acronyms.fields.short-description',
                'required' => !$options['lax'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'corporate.acronyms.fields.description',
                'required' => !$options['lax'],
            ])
            ->add('url', UrlType::class, [
                'label' => 'corporate.acronyms.fields.url',
                'required' => false,
            ])
            ->add('categories', CategoryChoiceType::class, [
                'label' => 'corporate.acronyms.fields.categories',
                'multiple' => true,
                'required' => false,
                'close_on_select' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'lax' => false,
            'translation_domain' => 'corporate',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_acronym';
    }
}
