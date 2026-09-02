<?php

declare(strict_types=1);

namespace App\Form\Type\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\FindAllServiceActivityQuery;
use App\CQRS\QueryBusInterface;
use App\Sdk\Resource\ServiceActivity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceActivityChoiceType extends AbstractType
{
    public function __construct(
        private readonly QueryBusInterface $queryBus
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choice_translation_domain' => false,
                'choices' => function (Options $options) {
                    /** @var ServiceActivity[] $serviceActivities */
                    $serviceActivities = $this->queryBus->dispatch(new FindAllServiceActivityQuery());

                    $choices = [];
                    foreach ($serviceActivities as $serviceActivity) {
                        $choices[$serviceActivity->name] = $serviceActivity->iri;
                    }

                    return $choices;
                },
                'placeholder' => '',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
