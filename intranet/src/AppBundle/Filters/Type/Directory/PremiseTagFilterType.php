<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Directory;

use AppBundle\Form\Type\Directory\Premise\PremiseTagChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PremiseTagFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tag', PremiseTagChoiceType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => Request::METHOD_GET,
        ]);
    }
}
