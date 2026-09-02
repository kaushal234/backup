<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class TroubleTicketCommentTransferType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $builder->getData();

        $nextLabel = match (true) {
            $options['request_information'] => 'trouble_ticket.button.request_information_next',
            $options['send_to_moo'] => 'trouble_ticket.button.send_to_moo_next',
            $options['send_to_mis'] => 'trouble_ticket.button.send_to_mis_next',
            $options['propose_solution'] => 'trouble_ticket.button.propose_solution_next',
            default => 'trouble_ticket.button.comment_next',
        };

        $commentLabel = match (true) {
            $options['request_information'] => 'trouble_ticket.fields.request_information',
            $options['send_to_moo'] => 'trouble_ticket.fields.send_to_moo',
            $options['send_to_mis'] => 'trouble_ticket.fields.send_to_mis',
            $options['propose_solution'] => 'trouble_ticket.fields.propose_solution',
            default => 'trouble_ticket.fields.comment',
        };

        $builder
            ->add('ccs', PeopleAutocompleteChoiceType::class, [
                'label' => 'contacts.mail.cc',
                'translation_domain' => 'contacts',
                'placeholder' => 'trainings.make_selection',
                'multiple' => true,
                'required' => false,
            ])
            ->add('file', FileType::class, [
                'label' => 'file_type.file_upload',
                'translation_domain' => 'file_type',
                'row_attr' => ['class' => 'm-0'],
                'required' => false,
                'mapped' => false,
            ])
            ->add('comment', CKEditorType::class, [
                'constraints' => [new NotBlank()],
                'label' => $commentLabel,
                'row_attr' => ['class' => 'm-0'],
                'config_name' => 'minimal_toolbar',
                'required' => true,
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info m-0 py-1 px-0 w-100 text-capitalize'],
            ]);

        if ($options['request_information']) {
            $type = PeopleAutocompleteChoiceType::class;
            $formOptions = [
                'label' => 'trouble_ticket.fields.assignee',
                'required' => true,
                'constraints' => [new NotBlank(['message' => $this->translator->trans('trouble_ticket.errors.assignee', [], 'trouble_ticket')])],
            ];

            if (!$this->security->isGranted('ACL_GG_MIS')) {
                $formOptions['query'] = [
                    'hidden' => false,
                    'disabled' => false,
                    'order' => [
                        'lastname' => 'ASC',
                        'firstname' => 'ASC',
                    ],
                    'excludeGroup' => 'GG_MIS',
                ];
            }

            $builder->add('assignee', $type, $formOptions);
        }
        if ($options['send_to_moo']) {
            $troubleTicket = $builder->getData();
            $choices = [];

            $users = array_filter([
                $troubleTicket['module']['operationalOwner'] ?? null,
                $troubleTicket['module']['keyUser'] ?? null,
            ]);

            foreach ($users as $user) {
                $label = \sprintf('%s, %s', $user['lastname'], $user['firstname']);
                $choices[$label] = $user['@id'];
            }

            foreach ($troubleTicket['module']['localKeyUsers'] as $localKeyUser) {
                $choices[\sprintf('%s, %s', $localKeyUser['lastname'], $localKeyUser['firstname'])] = $localKeyUser['@id'];
            }

            $builder->add('assignee', PeopleAutocompleteChoiceType::class, [
                'label' => 'trouble_ticket.fields.assignee',
                'required' => true,
                'choices' => $choices,
                'constraints' => [new NotBlank(message: $this->translator->trans('trouble_ticket.errors.assignee', [], 'trouble_ticket'))],
            ]);
        }

        $taskInSession = false;
        foreach (($this->requestStack->getSession()->get('tasks') ?? []) as $task) {
            if ($task['id'] === $data['id']) {
                $taskInSession = true;
            }
        }

        if ($taskInSession && \count($this->requestStack->getSession()->get('tasks')) > 1) {
            $builder->add('nextTask', SubmitType::class, [
                'label' => $nextLabel,
                'translation_domain' => 'trouble_ticket',
                'attr' => ['class' => 'btn btn-info m-0 py-1 px-2 w-100'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'trouble_ticket',
            'comment' => false,
            'request_information' => false,
            'send_to_moo' => false,
            'send_to_mis' => false,
            'propose_solution' => false,
        ]);
    }
}
