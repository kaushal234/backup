<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EquipmentSerialsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('component', EquipmentSerialsComponentChoiceType::class, [
                'label' => 'support.serials.fields.component',
                'translation_domain' => 'support',
            ])
            ->add('model', TextType::class, [
                'required' => false,
                'label' => 'service.equipment_record.fields.model',
                'translation_domain' => 'service',
            ])
            ->add('serial', TextType::class, [
                'label' => 'support.serials.fields.serial',
                'translation_domain' => 'support',
            ])
            ->add('brand', TextType::class, [
                'required' => false,
                'label' => 'support.serials.fields.brand',
                'translation_domain' => 'support',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            $options = $form->getConfig()->getOptions();

            $needsRebuild = false;

            if ('MANUAL' === ($data['component']['name'] ?? null) && (!isset($options['attr']['readonly']) || false === $options['attr']['readonly'])) {
                $options['attr']['readonly'] = true;
                $needsRebuild = true;
            }

            $parent = $form->getParent();
            if (null !== $parent && self::isDuplicatedSerial($data, $parent->getData() ?? [])) {
                $duplicateClasses = 'bg-danger-subtle border border-danger rounded p-2';
                $existingClass = $options['attr']['class'] ?? '';
                if (!str_contains($existingClass, 'border-danger')) {
                    $options['attr']['class'] = trim($existingClass.' '.$duplicateClasses);
                    $options['attr']['title'] = 'Duplicated serial (same component, model, serial and brand)';
                    $needsRebuild = true;
                }
            }

            if ($needsRebuild) {
                $parent = $form->getParent();
                $parent->remove($form->getName());
                $parent->add($form->getName(), \get_class($form->getConfig()->getType()->getInnerType()), $options);
            }
        });
    }

    /**
     * @param iterable<mixed> $siblings
     */
    private static function isDuplicatedSerial($serial, iterable $siblings): bool
    {
        $currentKey = self::buildSerialKey($serial);
        if (null === $currentKey) {
            return false;
        }

        $matches = 0;
        foreach ($siblings as $sibling) {
            if (self::buildSerialKey($sibling) === $currentKey) {
                ++$matches;
                if ($matches > 1) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function buildSerialKey($serial): ?string
    {
        if (!\is_array($serial) && !($serial instanceof \ArrayAccess)) {
            return null;
        }

        $serialNumber = (string) ($serial['serial'] ?? '');
        $model = (string) ($serial['model'] ?? '');
        if ('' === $serialNumber && '' === $model) {
            return null;
        }

        $componentKey = (string) ($serial['component']['@id'] ?? $serial['component']['name'] ?? '');
        $brand = (string) ($serial['brand'] ?? '');

        return implode('|', [$componentKey, $model, $serialNumber, $brand]);
    }
}
