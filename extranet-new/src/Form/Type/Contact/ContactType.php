<?php

declare(strict_types=1);

namespace App\Form\Type\Contact;

use App\DataTransferObject\Contact\ContactEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ContactType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('department', ChoiceType::class, [
                'choices' => [
                    'Sales' => 'Sales',
                    'Service' => 'Service',
                    'Spare Parts' => 'Spare Parts',
                ],
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'extranet.placeholder.department',
            ])
            ->add('message', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'resize:vertical; height:300px',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactEmail::class,
            'csrf_protection' => true,
            'action' => $this->urlGenerator->generate('contact:email'),
        ]);
    }
}
