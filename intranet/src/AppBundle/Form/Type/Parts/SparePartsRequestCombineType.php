<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Contracts\Translation\TranslatorInterface;

class SparePartsRequestCombineType extends AbstractType
{
    private readonly TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sparePartsRequests', SparePartsRequestChoiceType::class, [
                'label' => false,
                'sparePartsRequest' => $builder->getData(),
                'constraints' => [
                    new Count(['min' => 1, 'minMessage' => $this->translator->trans('spare_parts_request.combine.min_message', [], 'spare_parts_request')]),
                ],
                'attr' => [],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'spare_parts_request.combine.button',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'spare_parts_request',
        ]);
    }
}
