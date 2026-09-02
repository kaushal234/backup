<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use AppBundle\Form\Type\Common\ModuleAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Mis\Application\ApplicationChoiceType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\StimulusBundle\Helper\StimulusHelper;

abstract class AbstractTroubleTicketType extends AbstractType
{
    private const OPTION_FILTER = 'mis--trouble-ticket--option-filter';

    public function __construct(
        #[Autowire(service: 'stimulus.helper')]
        private readonly StimulusHelper $stimulusHelper,
        public readonly TranslatorInterface $translator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'trouble_ticket',
            'is_granted_open_on_behalf' => false,
            'is_granted_admin' => false,
            'default_application' => null,
            'statuses' => [],
        ]);

        $resolver->setAllowedTypes('is_granted_open_on_behalf', 'bool');
        $resolver->setAllowedTypes('is_granted_admin', 'bool');
        $resolver->setAllowedTypes('default_application', ['null', 'string']);
        $resolver->setAllowedTypes('statuses', 'array');
    }

    /**
     * application + module + typeCategory + type + url + shortDescription.
     */
    protected function addCommonFields(FormBuilderInterface $builder, ?string $defaultApplication = null): void
    {
        $applicationOptions = [
            'label' => 'trouble_ticket.fields.application',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new NotBlank(['message' => $this->translator->trans('trouble_ticket.errors.description', [], 'trouble_ticket')]),
            ],
        ];
        if (null !== $defaultApplication) {
            $applicationOptions['data'] = $defaultApplication;
        }

        $builder
            ->add('application', ApplicationChoiceType::class, $applicationOptions)
            ->add('module', ModuleAutocompleteChoiceType::class, [
                'label' => 'trouble_ticket.fields.module',
                'template' => '{{ name }} - {{ application.name }}',
                'query' => [
                    'order' => ['name' => 'ASC'],
                    'status' => 'ACTIVE',
                    'disabledForTroubleTicket' => false,
                ],
                'required' => true,
            ])
            ->add('typeCategory', ChoiceType::class, [
                'label' => 'trouble_ticket.fields.type',
                'mapped' => false,
                'required' => true,
                'placeholder' => '',
                'choice_translation_domain' => false,
                'choices' => [
                    'Incident' => 'Incident',
                    'Request' => 'Request',
                ],
                'attr' => $this->optionFilterAttr('category', 'apply'),
            ])
            ->add('type', TroubleTicketOptionChoiceType::class, [
                'label' => 'trouble_ticket.fields.option',
                'attr' => $this->optionFilterAttr('option', 'onOptionChange'),
                'required' => true,
            ])
            ->add('url', TextType::class, [
                'label' => 'trouble_ticket.fields.url',
                'required' => false,
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'trouble_ticket.fields.short_description',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans('trouble_ticket.errors.short_description', [], 'trouble_ticket')]),
                ],
            ]);
    }

    protected function addCreatedBy(FormBuilderInterface $builder, bool $isGrantedOpenOnBehalf): void
    {
        if ($isGrantedOpenOnBehalf) {
            $builder->add('createdBy', PeopleAutocompleteChoiceType::class, [
                'label' => 'tasks.assignor',
                'translation_domain' => 'messages',
            ]);

            return;
        }

        $builder->add('createdBy', HiddenType::class);
    }

    protected function addAssignee(FormBuilderInterface $builder): void
    {
        $builder->add('assignee', PeopleAutocompleteChoiceType::class, [
            'label' => 'tasks.assignee',
            'translation_domain' => 'messages',
            'required' => false,
        ]);
    }

    protected function addStatus(FormBuilderInterface $builder, array $statuses): void
    {
        if ([] === $statuses) {
            return;
        }

        $builder->add('status', ChoiceType::class, [
            'label' => 'fields.status',
            'translation_domain' => 'messages',
            'choice_translation_domain' => false,
            'choices' => array_combine($statuses, $statuses),
        ]);
    }

    protected function optionFilterAttr(string $target, ?string $action = null): array
    {
        $attributes = $this->stimulusHelper->createStimulusAttributes();
        $attributes->addTarget(self::OPTION_FILTER, $target);
        if (null !== $action) {
            $attributes->addAction(self::OPTION_FILTER, $action);
        }

        return $attributes->toArray();
    }
}
