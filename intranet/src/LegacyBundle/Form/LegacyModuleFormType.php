<?php

declare(strict_types=1);

namespace LegacyBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LegacyModuleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('module', ChoiceType::class, [
                'choice_translation_domain' => false,
                'choices' => [
                    'BP' => 'BP',
                    'CPA' => 'CPA',
                    'CRAB' => 'CRAB',
                    'CSR' => 'CSR',
                    'DEMO' => 'DEMO',
                    'DEROGATION' => 'DEROGATION',
                    'DMS' => 'DMS',
                    'EAP' => 'EAP',
                    'ER' => 'ER',
                    'FAQ' => 'FAQ',
                    'GWF' => 'GWF',
                    'MEAP' => 'MEAP',
                    'MIM' => 'MIM2',
                    'MIM (Legacy ID)' => 'MIM',
                    'MOM' => 'MOM',
                    'NCR' => 'NCR',
                    'PDC' => 'PDC',
                    'SB3' => 'SB3',
                    'SCAR' => 'SCAR',
                    'SEQ' => 'SEQ',
                    'SFR' => 'SFR2',
                    'SFR (Legacy ID)' => 'SFR',
                    'SOR' => 'SOR2',
                    'SOR (Legacy ID)' => 'SOR',
                    'SOL' => 'SOL',
                    'SPQ2' => 'SPQ2',
                    'SPR' => 'SPR',
                    'SPR2' => 'SPR2',
                    'TASK' => 'TASK2',
                    'TASK (Legacy ID)' => 'TASK',
                    'TTS' => 'TTS2',
                    'TTS (Legacy ID)' => 'TTS',
                    'TOC' => 'TOC',
                    'VWC' => 'VWC',
                    'WC' => 'WC',
                ], ]
            )
            ->add('id', TextType::class, [
                'attr' => [
                    'placeholder' => '#ID',
                ],
                'translation_domain' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'action' => '/en/private/calendar/calendar.php?m[0]=dashboard2&m[1]=byModuleID',
            'csrf_protection' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
