<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Directory;

use AppBundle\Form\Type\Common\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserActivityFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'directory',
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
        ]);
    }

    public function validate(array $data, ExecutionContextInterface $context): void
    {
        if ($data['createdAt']['after'] > $data['createdAt']['before']) {
            $context->buildViolation('directory.people.activity.invalidDateRange')
                ->setTranslationDomain('directory')
                ->atPath('createdAfter')
                ->addViolation();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createdAfter', DateTimePickerType::class, [
                'property_path' => '[createdAt][after]',
                'label' => 'directory.people.activity.fields.happened_after',
                'widget' => 'single_text',
                'translation_domain' => 'directory',
                'required' => false,
            ])
            ->add('createdBefore', DateTimePickerType::class, [
                'property_path' => '[createdAt][before]',
                'label' => 'directory.people.activity.fields.happened_before',
                'widget' => 'single_text',
                'translation_domain' => 'directory',
                'required' => false,
            ])
            ->add('filter', SubmitType::class)
        ;
    }
}
