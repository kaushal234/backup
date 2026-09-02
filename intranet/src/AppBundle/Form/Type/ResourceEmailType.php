<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceEmailType extends AbstractType
{
    private readonly array $languageChoices;

    public function __construct(array $languageChoices)
    {
        $this->languageChoices = array_flip($languageChoices);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('iri', HiddenType::class, [
                'required' => true,
            ])
            ->add('link', HiddenType::class, [
                'required' => true,
            ])
            ->add('to', PeopleAutocompleteChoiceType::class, [
                'required' => true,
                'label' => 'fields.to',
                'multiple' => true,
                'template' => '{{email}}',
                'id_key' => '[email]',
            ])
            ->add('cc', PeopleAutocompleteChoiceType::class, [
                'required' => false,
                'label' => 'fields.cc',
                'multiple' => true,
                'template' => '{{email}}',
                'id_key' => '[email]',
            ])
            ->add('bcc', PeopleAutocompleteChoiceType::class, [
                'required' => false,
                'label' => 'fields.bcc',
                'multiple' => true,
                'template' => '{{email}}',
                'id_key' => '[email]',
            ])
            ->add('subject', TextType::class, [
                'label' => 'fields.subject',
            ])
            ->add('note', TextType::class, [
                'label' => 'fields.note',
                'required' => false,
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'fields.language',
                'choices' => $this->languageChoices,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'resource_email',
        ]);
    }

    public function getName(): string
    {
        return 'app_resource_email';
    }
}
