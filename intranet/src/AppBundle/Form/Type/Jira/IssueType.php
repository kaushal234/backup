<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Jira;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IssueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cleanDescription = '';
        if ($options['description']) {
            $cleanDescription = strip_tags($options['description']);
        }

        $builder
            ->add('summary', TextType::class, [
                'required' => true,
                'label' => 'fields.short-description',
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'fields.description',
                'attr' => [
                    'style' => 'resize:vertical; height:200px',
                ],
                'data' => $cleanDescription,
            ])
            ->add('type', IssueTypeChoiceType::class, [
                'required' => true,
                'label' => 'trouble_ticket.fields.type',
                'translation_domain' => 'trouble_ticket',
                'filters' => [
                    'projectId' => $options['projectId'] ?? null,
                ],
            ])
            ->add('priority', PriorityChoiceType::class, [
                'required' => true,
                'label' => 'trouble_ticket.fields.priority',
                'translation_domain' => 'trouble_ticket',
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
            'projectId' => null,
            'translation_domain' => 'messages',
            'description' => null,
            'csrf_protection' => false,
        ]);
    }
}
