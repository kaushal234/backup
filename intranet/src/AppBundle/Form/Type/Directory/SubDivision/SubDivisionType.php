<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\SubDivision;

use AppBundle\Form\Type\Directory\Division\DivisionChoiceType;
use AppBundle\Form\Type\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubDivisionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $subDivision = $builder->getData();
        $builder
            ->add('name', TextType::class, [
                'label' => 'fields.name',
                'translation_domain' => 'messages',
            ])
            ->add('division', DivisionChoiceType::class, [
                'label' => 'directory.division.name',
                'placeholder' => 'directory.business_unit.make_selection',
            ])
            ->add('logo', ImageType::class, [
                'label' => 'directory.location.fields.logo',
                'required' => false,
                'mapped' => false,
                'image_url' => isset($subDivision['logo']) && null !== $subDivision['logo'] ? $subDivision['logo']['filePath'] : null,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
        ]);
    }
}
