<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class TypeChoiceType extends AbstractType
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
            'filters' => [],
            'choices' => function (Options $options) {
                $collection = $this->dataProvider->findAll(
                    'mis/types',
                    [],
                    ['displayedOrder' => 'ASC']
                );

                $choices = [];
                foreach ($collection as $item) {
                    $choices[\sprintf('%s - %s', $item['type'], $this->translator->trans('trouble_ticket.form.option_value.'.$item['description'], [], 'trouble_ticket'))] = $item[$options['key']];
                }

                return ['' => ''] + $choices;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return SelectFormType::class;
    }
}
