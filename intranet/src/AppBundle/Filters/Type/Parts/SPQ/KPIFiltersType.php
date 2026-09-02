<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Parts\SPQ;

use AppBundle\Form\Type\Directory\Location\SparePartsHubChoiceType;
use AppBundle\Form\Type\Directory\People\PartsMemberChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KPIFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $people = empty($options['poster']) ? 'quoter' : 'poster';
        $builder
            ->add('locations', SparePartsHubChoiceType::class, [
                'label' => 'spq.quotations.fields.sph',
                'placeholder' => 'spq.form.make_selection',
                'required' => false,
                'translation_domain' => 'spq',
                'extra_choices' => ['ALL' => 'all'],
            ])
            ->add($people, PartsMemberChoiceType::class, [
                'label' => "spq.quotations.fields.$people",
                'placeholder' => 'spq.form.make_selection',
                'required' => false,
                'extra_choices' => ['ALL' => 'all'],
            ])
            ->add('cunos', TextType::class, [
                'label' => 'spq.quotations.fields.cuno',
                'required' => false,
                'data' => [],
            ])
            ->add('payableService', ChoiceType::class, [
                'label' => 'spq.quotations.fields.payableService',
                'required' => false,
                'placeholder' => '',
                'choices' => ['yes' => 1, 'no' => 0],
                'choice_translation_domain' => 'messages',
            ])
        ;
        $builder->get('cunos')
                ->addModelTransformer(new CallbackTransformer(
                    static fn ($cunosAsArray): string => implode(',', $cunosAsArray),
                    static fn ($cunosAsString) => array_map('trim', explode(',', (string) $cunosAsString))
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'spq',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'poster' => false,
            'method' => Request::METHOD_GET,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_spq_kpi';
    }
}
