<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use AppBundle\Form\Type\LanguageChoiceType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class ManualType extends AbstractType
{
    public function __construct(
        private readonly DataProvider $dataProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        $collection = $this->dataProvider->findAll(
            'support/manual_document_categories',
            [],
            ['name']
        );

        foreach ($collection as $category) {
            $choices[$category['name']] = $category['@id'];
        }

        $builder
            ->add('description', TextType::class, [
                'label' => 'support.manual.fields.description',
            ])
            ->add('features', TextareaType::class, [
                'label' => 'support.manual.fields.features',
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('language', LanguageChoiceType::class, [
                'label' => 'support.manual.fields.language',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'RELEASED' => 'RELEASED',
                    'PRELIMINARY' => 'PRELIMINARY',
                ],
                'label' => 'support.manual.fields.status',
            ])
            ->add('documents', CollectionType::class, [
                'label' => false,
                'entry_type' => ManualDocumentType::class,
                'entry_options' => [
                    'show_manual_parts' => false,
                    'label' => false,
                    'manual_document_category_choices' => $choices,
                ],
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'support',
        ]);
    }
}
