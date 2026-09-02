<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\FirstArticleQualification;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PlanLineType extends AbstractType
{
    private const array COMPLETION_CHOICES = ['0 %' => 0, '25 %' => 25, '50 %' => 50, '75 %' => 75, '100 %' => 100];
    private const int COMPLETED_RATE = 100;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            $form = $event->getForm();
            $line = $event->getData() ?? [];

            $typeIri = $this->extractTypeIri($line);
            $line['type'] = $typeIri;
            $event->setData($line);

            $isNewLine = empty($line['id']);
            $type = $this->findType($options['plan_item_types'], $typeIri);

            $priorAllowed = $type ? (bool) $type['requestablePriorDelivery'] : $isNewLine;
            $purchaseAllowed = $type ? (bool) $type['requestableAtPurchaseOrder'] : $isNewLine;

            $isCompleted = self::COMPLETED_RATE === (int) ($line['completionRate'] ?? 0);
            $lineEditable = $options['plan_editable'] && !$isCompleted;
            $completionEditable = $options['plan_completion_editable'] && !$isCompleted;

            if ($isNewLine) {
                $form->add('type', ChoiceType::class, [
                    'choices' => array_keys($options['plan_item_types']),
                    'choice_label' => static fn (string $iri) => $options['plan_item_types'][$iri]['description'] ?? $iri,
                    'placeholder' => '—',
                    'disabled' => !$options['plan_editable'],
                ]);
            } else {
                $form->add('type', HiddenType::class);
            }

            $form
                ->add('id', HiddenType::class, ['required' => false])
                ->add('description', TextareaType::class, [
                    'required' => false,
                    'disabled' => !$lineEditable,
                    'empty_data' => '',
                ])
                ->add('comment', TextareaType::class, [
                    'required' => false,
                    'disabled' => !$lineEditable,
                ])
                ->add('requestedPriorDelivery', CheckboxType::class, [
                    'required' => false,
                    'disabled' => !$lineEditable || !$priorAllowed,
                ])
                ->add('requestedAtPurchaseOrder', CheckboxType::class, [
                    'required' => false,
                    'disabled' => !$lineEditable || !$purchaseAllowed,
                ])
                ->add('completionRate', ChoiceType::class, [
                    'choices' => self::COMPLETION_CHOICES,
                    'disabled' => !$completionEditable,
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'plan_item_types' => [],
            'plan_editable' => false,
            'plan_completion_editable' => false,
        ]);
        $resolver->setAllowedTypes('plan_item_types', 'array');
        $resolver->setAllowedTypes('plan_editable', 'bool');
        $resolver->setAllowedTypes('plan_completion_editable', 'bool');

        $resolver->setNormalizer(
            'plan_item_types',
            static fn ($options, array $types) => array_column($types, null, '@id')
        );
    }

    private function extractTypeIri(array $line): ?string
    {
        if (empty($line['type'])) {
            return null;
        }

        return \is_array($line['type']) ? ($line['type']['@id'] ?? null) : $line['type'];
    }

    private function findType(array $types, ?string $iri): ?array
    {
        foreach ($types as $type) {
            if (($type['@id'] ?? null) === $iri) {
                return $type;
            }
        }

        return null;
    }
}
