<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Application;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $application = $builder->getData();

        $builder
            ->add('name', TextType::class, [
                'label' => 'fields.name',
                'translation_domain' => 'messages',
            ])
            ->add('jiraProjectId', IntegerType::class, [
                'label' => 'mis_application.fields.jiraProjectId',
                'translation_domain' => 'mis_application',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
