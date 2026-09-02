<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Add a Trouble Ticket: the common fields plus a rich-text description and CCs.
 */
class TroubleTicketType extends AbstractTroubleTicketType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addCommonFields($builder, $options['default_application']);

        $builder
            ->add('description', CKEditorType::class, [
                'label' => 'fields.description',
                'translation_domain' => 'messages',
                'config_name' => 'simple',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans('trouble_ticket.errors.description', [], 'trouble_ticket')]),
                ],
            ])
            // Severity ("iFactor") is not shown on the add form: the option-filter controller derives
            // it from the selected option's data-indice and writes it here, so it is submitted and
            // stored on create (mirroring the original form). Target-only (no action of its own).
            ->add('indiceFactor', HiddenType::class, [
                'required' => false,
                'attr' => $this->optionFilterAttr('indice'),
            ])
            ->add('ccs', PeopleAutocompleteChoiceType::class, [
                'label' => 'Cc',
                'translation_domain' => 'contacts',
                'required' => false,
                'multiple' => true,
            ])
        ;

        $this->addCreatedBy($builder, $options['is_granted_open_on_behalf']);
    }
}
