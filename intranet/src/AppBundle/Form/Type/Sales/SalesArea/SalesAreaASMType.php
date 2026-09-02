<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\SalesArea;

use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesAreaASMType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('asm', ASMChoiceType::class, [
                'label' => 'sales.sales_areas.asm',
                'required' => true,
                'exclude' => $options['exclude'],
                'filters' => ['businessUnit.location.network' => $options['network']],
            ])
            ->add('sso', SSOChoiceType::class, [
                'required' => true,
                'label' => 'directory.department.fields.sso',
                'translation_domain' => 'directory',
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'capability.sso' => 1,
                    'network' => $options['network'],
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'sales.sales_areas.add',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales',
            'exclude' => [],
            'network' => null,
        ]);
    }

    public function getName(): string
    {
        return 'app_sales_areas_asm';
    }
}
