<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechnicianOnCallTagChoiceType extends AbstractType
{
    public function __construct(
        private readonly DataProvider $dataProvider,
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
            'required' => false,
            'multiple' => true,
            'label' => 'toc.fields.tags.label',
            'choice_translation_domain' => 'technician_on_call',
            'choices' => function (Options $options) {
                $collection = $this->dataProvider->findAll(
                    'technician_on_call_tags',
                    [],
                    ['name']
                );

                $choices = [];
                foreach ($collection as $technicianonCallTag) {
                    $choices[$technicianonCallTag['name']] = $technicianonCallTag[$options['key']];
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_technician_on_call_tag_choice';
    }
}
