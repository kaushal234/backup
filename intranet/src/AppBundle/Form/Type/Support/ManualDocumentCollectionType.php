<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class ManualDocumentCollectionType extends AbstractType
{
    public function __construct(
        private readonly DataProvider $dataProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $manualDocumentCategoryChoices = [];
        $collection = $this->dataProvider->findAll(
            'support/manual_document_categories',
            [],
            ['name']
        );
        foreach ($collection as $category) {
            $manualDocumentCategoryChoices[$category['name']] = $category['@id'];
        }

        $builder
            ->add('documents', CollectionType::class, [
                'label' => false,
                'entry_type' => ManualDocumentType::class,
                'entry_options' => [
                    'show_manual_parts' => false,
                    'label' => false,
                    'manual_document_category_choices' => $manualDocumentCategoryChoices,
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
