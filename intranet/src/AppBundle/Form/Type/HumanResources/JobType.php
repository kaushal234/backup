<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\HumanResources;

use ApiBundle\Form\Type\ResourceCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('businessUnit', ResourceCollectionType::class, [
                'label' => 'directory.location.fields.businessUnit',
                'required' => true,
                'resource' => 'business_units',
                'property' => 'name',
                'placeholder' => 'directory.location.make_selection',
                'translation_domain' => 'directory',
            ])
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'jobs.fields.title',
            ])
            ->add('experience', TextType::class, [
                'required' => true,
                'label' => 'jobs.fields.experience',
            ])
            ->add('diploma', TextType::class, [
                'required' => true,
                'label' => 'jobs.fields.diploma',
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'jobs.fields.description',
                'attr' => [
                    'style' => 'resize:vertical; height:200px',
                ],
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'jobs.fields.open',
            ])
            ->add('synchronized', CheckboxType::class, [
                'required' => false,
                'label' => 'jobs.fields.synchronized',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
                'translation_domain' => 'messages',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'job',
            'csrf_protection' => false,
        ]);
    }
}
