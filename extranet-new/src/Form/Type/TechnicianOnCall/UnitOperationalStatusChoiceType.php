<?php

declare(strict_types=1);

namespace App\Form\Type\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\FindAllUnitOperationalStatusQuery;
use App\CQRS\QueryBusInterface;
use App\Sdk\Resource\UnitOperationalStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UnitOperationalStatusChoiceType extends AbstractType
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly TranslatorInterface $translator,
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
                    /** @var UnitOperationalStatus[] $unitOperationalStatuses */
                    $unitOperationalStatuses = $this->queryBus->dispatch(new FindAllUnitOperationalStatusQuery());

                    $choices = [];
                    foreach ($unitOperationalStatuses as $unitOperationalStatus) {
                        $choiceName = $this->translator->trans(\sprintf('%s.%s', 'extranet.fields.unit_operational_status', mb_strtolower($unitOperationalStatus->name)), [], 'messages');
                        $choices[$choiceName] = $unitOperationalStatus->iri;
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
