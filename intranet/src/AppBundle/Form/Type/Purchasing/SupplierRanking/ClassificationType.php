<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\SupplierRanking;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ClassificationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'fields.name',
                'required' => true,
                'constraints' => [new Length(max: 100), new NotBlank()],
                'translation_domain' => 'messages',
            ])
            ->add('description', TextType::class, [
                'label' => 'fields.description',
                'required' => false,
                'translation_domain' => 'messages',
            ])
            ->add('isSupplierApproved', CheckboxType::class, [
                'label' => 'classification.fields.supplier_approved',
                'required' => false,
            ])
            ->add('workflowLevel', IntegerType::class, [
                'label' => 'classification.fields.workflow_level',
                'required' => false,
            ])
            ->add('color', TextType::class, [
                'label' => 'classification.fields.color',
                'required' => false,
            ])
            ->add('targetClassifications', ClassificationChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => 'supplier_ranking']);
    }
}
