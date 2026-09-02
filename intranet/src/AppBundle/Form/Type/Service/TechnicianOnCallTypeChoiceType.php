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

class TechnicianOnCallTypeChoiceType extends AbstractType
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
            'choice_translation_domain' => false,
            'choices' => function (Options $options): array {
                $collection = $this->dataProvider->findAll('service/technician_on_call_types');

                $choices = [];
                foreach ($collection as $technicianOnCallType) {
                    $choices[$technicianOnCallType['name']] = $technicianOnCallType[$options['key']];
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
