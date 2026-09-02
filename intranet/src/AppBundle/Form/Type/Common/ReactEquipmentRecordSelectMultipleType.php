<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReactEquipmentRecordSelectMultipleType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'resource' => null,
            'multiple' => true,
            'filterProducts' => [],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['filterProducts'] = $options['filterProducts'];

        $view->vars['reactDefaultValue'] = [];
        foreach ($view->vars['data'] as $value => $label) {
            $view->vars['reactDefaultValue'][] = [
                'value' => $value,
                'label' => $label,
            ];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            static function ($equipmentRecords) {
                $formValues = [];
                foreach ($equipmentRecords as $equipmentRecord) {
                    $formValues[$equipmentRecord['@id']] = \sprintf('%s / %s - %s', $equipmentRecord['type'], $equipmentRecord['model'], $equipmentRecord['serialNumber']);
                }

                return $formValues;
            },
            static function ($string) {
                return $string;
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
