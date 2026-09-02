<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Position;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionClassificationBatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('positionClassifications', CollectionType::class, [
                'entry_type' => PositionClassificationType::class,
                'entry_options' => ['businessUnit' => $options['businessUnit']],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['businessUnit' => null])
            ->setRequired(['businessUnit'])
        ;
    }
}
