<?php

declare(strict_types=1);

namespace App\Filter\Type;

use App\Form\Location\LocationChoiceType;
use App\Form\Supplier\SupplierChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ReportFilterType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('supplierNumber', SupplierChoiceType::class, [
                'label' => 'title.report.supplier',
                'translation_domain' => 'messages',
                'required' => false,
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-control mb-1'],
            ])
            ->add('factory', LocationChoiceType::class, [
                'label' => 'title.report.buying_site',
                'translation_domain' => 'messages',
                'required' => true,
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-control mb-1'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'action' => $this->urlGenerator->generate('kpi:index'),
        ]);
    }
}
