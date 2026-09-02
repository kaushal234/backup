<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TroubleTicketCloseType extends AbstractType
{
    public const SATISFACTION_CHOICES = [
        'Not satisfied at all' => 'Not satisfied at all',
        'Not much satisfied' => 'Not much satisfied',
        'Satisfied' => 'Satisfied',
        'Very satisfied' => 'Very satisfied',
    ];
    private const SOLVED = 'SOLVED';

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('id', HiddenType::class);
        $builder->add('ccs', PeopleAutocompleteChoiceType::class, [
            'label' => 'contacts.mail.cc',
            'translation_domain' => 'contacts',
            'multiple' => true,
            'required' => false,
        ]);
        $builder->add('comment', TextareaType::class, [
            'label' => 'trouble_ticket.message.reason_close',
            'required' => true,
            'error_bubbling' => true,
            'attr' => ['rows' => 6],
            'constraints' => [
                new NotBlank(message: $this->translator->trans('trouble_ticket.errors.comment', [], 'trouble_ticket')),
            ],
        ]);
        $builder->add('status', SelectFormType::class, [
            'label' => 'fields.status',
            'translation_domain' => 'messages',
            'choice_translation_domain' => false,
            'required' => true,
            'choices' => array_combine($options['statuses'], $options['statuses']),
            'attr' => [
                'data-model' => 'status',
                'data-action' => 'change->live#update',
            ],
            'constraints' => [
                new NotBlank(message: $this->translator->trans('trouble_ticket.errors.status', [], 'trouble_ticket')),
            ],
        ]);
        $builder->add('satisfaction', ChoiceType::class, [
            'label' => 'trouble_ticket.fields.rating',
            'required' => false,
            'expanded' => true,
            'placeholder' => false,
            'choice_translation_domain' => false,
            'choices' => self::SATISFACTION_CHOICES,
        ]);
        $builder->add('satisfactionComment', TextareaType::class, [
            'label' => 'trouble_ticket.fields.additional_comment',
            'required' => false,
            'attr' => [
                'placeholder' => $this->translator->trans('trouble_ticket.message.additional_comment_placeholder', [], 'trouble_ticket'),
                'rows' => 4,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'trouble_ticket',
            'statuses' => [],
            'tasks' => false,
            'constraints' => [
                new Callback([$this, 'validateSatisfaction']),
            ],
        ]);
        $resolver->setAllowedTypes('statuses', 'array');
        $resolver->setAllowedTypes('tasks', 'bool');
    }

    /**
     * Satisfaction is required only when the chosen status is SOLVED, mirroring the
     * original client-side rule (validationClose.ts).
     *
     * @param array<string, mixed>|null $data
     */
    public function validateSatisfaction(?array $data, ExecutionContextInterface $context): void
    {
        if (null === $data) {
            return;
        }
        if (self::SOLVED !== ($data['status'] ?? null)) {
            return;
        }
        if (!empty($data['satisfaction'])) {
            return;
        }
        $context->buildViolation($this->translator->trans('trouble_ticket.errors.satisfaction', [], 'trouble_ticket'))
            ->atPath('satisfaction')
            ->addViolation();
    }
}
