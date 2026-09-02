<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Catalogue;

use AppBundle\Validator\Constraints\LeadTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class LeadTimeBatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('leadTimes', CollectionType::class, [
                'entry_type' => LeadTimeType::class,
                'constraints' => [new LeadTime()],
            ])
            ->add('fullUpdate', CheckboxType::class, [
                'required' => false,
                'data' => true,
                'label' => 'lead_time.full_update',
                'translation_domain' => 'catalogue',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }
}
