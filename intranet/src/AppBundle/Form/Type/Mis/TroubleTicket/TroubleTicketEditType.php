<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class TroubleTicketEditType extends AbstractTroubleTicketType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['is_granted_admin']) {
            $this->addAssignee($builder);
            $this->addStatus($builder, $options['statuses']);
        }

        $this->addCommonFields($builder, $options['default_application']);

        // Severity ("IFactor") override. Optional; empty = keep whatever the option implies.
        $builder->add('indiceFactor', ChoiceType::class, [
            'label' => 'trouble_ticket.fields.indice_factor',
            'required' => false,
            'placeholder' => '',
            'choice_translation_domain' => false,
            'choices' => [
                'IF 1' => 'IF 1',
                'IF 10' => 'IF 10',
                'IF 100' => 'IF 100',
                'IF 1000' => 'IF 1000',
            ],
        ]);

        $builder->add('dueDate', DateType::class, [
            'label' => 'trouble_ticket.fields.due_date',
            'widget' => 'single_text',
            'input' => 'datetime_immutable',
            'required' => false,
        ]);
        $builder->add('comment', CKEditorType::class, [
            'label' => 'trouble_ticket.fields.reason_for_editing',
            'config_name' => 'simple',
            'error_bubbling' => true,
            'constraints' => [
                new NotBlank(['message' => $this->translator->trans('trouble_ticket.errors.reason_for_editing_required', [], 'trouble_ticket')]),
            ],
        ]);

        $this->addCreatedBy($builder, $options['is_granted_open_on_behalf']);
    }
}
