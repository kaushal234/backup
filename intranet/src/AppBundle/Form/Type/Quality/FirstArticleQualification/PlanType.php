<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\FirstArticleQualification;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('plan', CollectionType::class, [
            'entry_type' => PlanLineType::class,
            'entry_options' => [
                'plan_item_types' => $options['plan_item_types'],
                'plan_editable' => $options['plan_editable'],
                'plan_completion_editable' => $options['plan_completion_editable'],
            ],
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => static fn (?array $line) => empty($line['type']),
            'label' => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($options): void {
            if (!$options['plan_editable']) {
                return;
            }

            $data = $event->getData();

            if (empty($data['plan'])) {
                $data['plan'] = [[]];
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'plan_item_types' => [],
            'plan_editable' => false,
            'plan_completion_editable' => false,
        ]);
        $resolver->setAllowedTypes('plan_item_types', 'array');
        $resolver->setAllowedTypes('plan_editable', 'bool');
        $resolver->setAllowedTypes('plan_completion_editable', 'bool');
    }
}
