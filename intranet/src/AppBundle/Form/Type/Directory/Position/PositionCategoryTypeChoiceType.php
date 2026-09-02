<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Position;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Controller\Directory\PositionCategoryTypeController;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class PositionCategoryTypeChoiceType extends AbstractType
{
    public function __construct(
        private readonly DataProvider $dataProvider,
        private readonly TranslatorInterface $translator,
    ) {
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
        $resolver->setDefaults([
            'key' => '@id',
            'choice_translation_domain' => false,
            'choices' => function (Options $options) {
                $collection = $this->dataProvider->findAll(PositionCategoryTypeController::RESOURCE_URL, [], ['name']);

                $choices = [];
                foreach ($collection as $item) {
                    $choices[$item['name']] = $item['@id'];
                }

                return ['' => ''] + $choices;
            },
            'attr' => ['data-no_results_text' => $this->translator->trans('no_results', [], 'messages')],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
