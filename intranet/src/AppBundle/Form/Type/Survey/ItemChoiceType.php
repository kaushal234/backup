<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Survey;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Controller\Survey\ItemController;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class ItemChoiceType extends AbstractType
{
    private readonly DataProvider $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new IrisResourceToIdTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $collection = $this->dataProvider->findAll(ItemController::RESOURCE_URL, []);

        $choices = [];

        foreach ($collection as $item) {
            $value = mb_substr((string) $item['description'], 0, 60);
            $value .= mb_strlen((string) $item['description']) > mb_strlen($value) ? '...' : '';
            $choices[$value] = $item['@id'];
        }

        $resolver->setDefaults([
            'choices' => $choices,
            'placeholder' => 'make_selection',
            'translation_domain' => 'surveys',
            'label' => 'fields_labels.item',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_survey_item_choice';
    }
}
