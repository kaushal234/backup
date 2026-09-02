<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Support;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Length;

class ManualFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('partNumber', TextType::class, [
                'required' => false,
                'label' => 'support.manual_document_parts.filter_part_number',
                'attr' => ['placeholder' => 'ex: 1030122'],
                'constraints' => [
                    new AtLeastOneOf([
                        new Blank(),
                        new Length(['min' => 3]),
                    ]),
                ],
            ])
            ->add('partDescription', TextType::class, [
                'required' => false,
                'label' => 'support.manual_document_parts.filter_part_description',
                'attr' => ['placeholder' => 'ex: GEAR SHIFTER'],
                'constraints' => [
                    new AtLeastOneOf([
                        new Blank(),
                        new Length(['min' => 3]),
                    ]),
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'support',
            'csrf_protection' => false,
        ]);
    }
}
