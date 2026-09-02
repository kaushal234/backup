<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\FreightForwarder;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Controller\Sales\FreightForwarderController;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class FreightForwarderChoiceType extends AbstractType
{
    /**
     * @var DataProvider
     */
    protected $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

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
            'filters' => [
                'pagination' => 0,
            ],
            'choice_translation_domain' => false,
            'choices' => function (Options $options) {
                $filters = $options['filters'];
                $collection = $this->dataProvider->findAll(
                    FreightForwarderController::RESOURCE_URL,
                    $filters,
                    ['name']
                );

                $choices = [];

                foreach ($collection as $freightForwarder) {
                    $value = null !== $freightForwarder['location'] ? \sprintf('%s - %s', $freightForwarder['name'], $freightForwarder['location']['name']) : $freightForwarder['name'];
                    $choices[$value] = $freightForwarder['@id'];
                }

                return $choices;
            },
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
