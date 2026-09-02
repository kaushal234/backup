<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Project;

use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\StimulusBundle\Helper\StimulusHelper;

class ProjectPhaseType extends AbstractType
{
    public const string STIMULUS_MIN_DATE = 'datepicker-target-min-date';

    private bool $isGrantedCio;

    public function __construct(
        #[Autowire(service: 'stimulus.helper')]
        private readonly StimulusHelper $stimulusHelper,
        private readonly Security $security,
    ) {
        $this->isGrantedCio = $this->security->isGranted('FEATURE_MIS_PROJECT_CIO_EDIT');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', HiddenType::class)
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                [$this, 'onPreSetData']
            )
        ;

        $builder->get('number')->addModelTransformer(new CallbackTransformer(
            static fn ($value) => $value,
            static fn ($value) => null !== $value ? (int) $value : 0
        ));
    }

    public function onPreSetData(PreSetDataEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!isset($data['number'])) {
            throw new \InvalidArgumentException('Each phases must contain a number key.');
        }

        $project = $form->getParent()?->getParent()->getData();
        $activePhase = $event->getForm()->getConfig()->getOption('active_phase');
        $isNew = $event->getForm()->getConfig()->getOption('is_new');
        $disabled = $data['number'] < $activePhase;
        $currentDate = (new \DateTime())->format('Y-m-d');
        $minDate = $this->getPreviousRevisedDueDate($data['number'], $form);
        $targetAttribute = \sprintf('data-%s-target-value', self::STIMULUS_MIN_DATE);
        $isGrantedProjectManagerOrMisOwner = false;
        if (false === $isNew && null !== $project) {
            $isGrantedProjectManagerOrMisOwner = \in_array(
                $this->security->getToken()->getUser()->getUserIdentifier(),
                [$project['projectManager']['username'], $project['misOwner']['username']],
                true)
            ;
        }

        $form
            ->add('estimatedClosureAt', DatePickerType::class, [
                'label' => 'mis_project.fields.due_date',
                'disabled' => $disabled || (!$isNew && !$this->isGrantedCio),
                'restrictions' => [
                    'minDate' => $currentDate,
                ],
            ])
            ->add('estimatedHours', IntegerType::class, [
                'label' => 'mis_project.fields.estimated_hours',
                'disabled' => $disabled,
            ])
        ;

        if (false === $isNew && ($this->isGrantedCio || $isGrantedProjectManagerOrMisOwner)) {
            $attr['data-controller'] = self::STIMULUS_MIN_DATE;
            if ($data['number'] < 4) {
                $attr[$targetAttribute] = \sprintf('project_phases_%s_revisedClosureAt', $data['number'] + 1);
            }

            $form->add('revisedClosureAt', DatePickerType::class, [
                'required' => false,
                'label' => 'mis_project.fields.due_date_revised',
                'disabled' => $disabled,
                'restrictions' => [
                    'minDate' => $minDate,
                ],
                'attr' => $attr,
            ]);
            $form->add('revisedEstimatedHours', IntegerType::class, [
                'required' => false,
                'label' => 'mis_project.fields.revised_estimated_hours',
                'disabled' => $disabled,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'is_new' => true,
            'active_phase' => null,
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        parent::finishView($view, $form, $options);

        if (isset($view->children['revisedClosureAt'])) {
            $attr = $this->stimulusHelper->createStimulusAttributes();
            $attr->addController(\sprintf('%s %s', DatePickerType::STIMULUS_CONTROLLER, self::STIMULUS_MIN_DATE));
            $view->children['revisedClosureAt']->vars['attr'] = [
                ...$view->children['revisedClosureAt']->vars['attr'],
                ...$attr->toArray(),
            ];
        }
    }

    private function getPreviousRevisedDueDate(int $currentPhase, FormInterface $form): string
    {
        $phases = $form->getParent()?->getData();

        if (isset($phases[$currentPhase - 1]['revisedClosureAt'])) {
            return $phases[$currentPhase - 1]['revisedClosureAt'];
        }

        return (new \DateTime())->format('Y-m-d');
    }
}
