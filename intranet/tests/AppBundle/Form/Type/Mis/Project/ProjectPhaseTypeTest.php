<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Project;

use AppBundle\Form\DataTransformer\DatePickerModelTransformer;
use AppBundle\Form\Type\Common\DatePickerType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\StimulusBundle\Helper\StimulusHelper;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class ProjectPhaseTypeTest extends TypeTestCase
{
    private MockObject&TranslatorInterface $translator;

    private StimulusHelper $stimulusHelper;

    private DatePickerModelTransformer $transformer;

    private MockObject&Security $security;

    protected function setUp(): void
    {
        $twig = new Environment(new ArrayLoader(), ['cache' => false, 'debug' => true]);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->stimulusHelper = new StimulusHelper($twig);
        $this->security = $this->createMock(Security::class);
        $this->transformer = new DatePickerModelTransformer();

        parent::setUp();
    }

    public function testEmptyFormWithPhases(): void
    {
        $security = $this->createMock(Security::class);
        $security
            ->expects($this->once())
            ->method('isGranted')
            ->with('FEATURE_MIS_PROJECT_CIO_EDIT')
            ->willReturn(false);

        $factory = Forms::createFormFactoryBuilder()
            ->addExtensions([
                new PreloadedExtension([
                    new ProjectPhaseType($this->stimulusHelper, $security),
                    new DatePickerType(
                        $this->translator,
                        $this->transformer,
                        $this->stimulusHelper
                    ),
                ], []),
            ])
            ->getFormFactory();

        $form = $factory->create(ProjectPhaseType::class, ['number' => 4]);

        $this->assertTrue($form->has('number'));
        $this->assertTrue($form->has('estimatedClosureAt'));
        $this->assertTrue($form->has('estimatedHours'));
        $this->assertFalse($form->has('revisedClosureAt'));
        $this->assertFalse($form->has('revisedEstimatedHours'));
        $this->assertSame(1, $form->get('number')->submit('1')->getData());
    }

    public function testCioEditDisablePhase(): void
    {
        $form = $this->factory->create(ProjectPhaseType::class, ['number' => 0], [
            'is_new' => false,
            'active_phase' => 1,
        ]);

        $this->assertTrue($form->has('number'));
        $this->assertTrue($form->has('estimatedClosureAt'));
        $this->assertTrue($form->has('estimatedHours'));
        $this->assertTrue($form->has('revisedClosureAt'));
        $this->assertTrue($form->has('revisedEstimatedHours'));
        $this->assertTrue($form->get('estimatedClosureAt')->isDisabled());
        $this->assertTrue($form->get('estimatedHours')->isDisabled());
        $this->assertTrue($form->get('revisedClosureAt')->isDisabled());
        $this->assertTrue($form->get('revisedEstimatedHours')->isDisabled());
    }

    public function testCioEditEnabledPhase(): void
    {
        $form = $this->factory->create(ProjectPhaseType::class, ['number' => 1], [
            'is_new' => false,
            'active_phase' => 1,
        ]);

        $this->assertTrue($form->has('number'));
        $this->assertTrue($form->has('estimatedClosureAt'));
        $this->assertTrue($form->has('estimatedHours'));
        $this->assertTrue($form->has('revisedClosureAt'));
        $this->assertTrue($form->has('revisedEstimatedHours'));
        $this->assertFalse($form->get('estimatedClosureAt')->isDisabled());
        $this->assertFalse($form->get('estimatedHours')->isDisabled());
        $this->assertFalse($form->get('revisedClosureAt')->isDisabled());
        $this->assertFalse($form->get('revisedEstimatedHours')->isDisabled());

        $config = $form->get('revisedClosureAt')->getConfig();
        $this->assertSame(
            (new \DateTime())->format('Y-m-d'),
            $config->getOption('restrictions')['minDate']
        );
        $attr = $config->getOption('attr');
        $this->assertSame('datepicker-target-min-date', $attr['data-controller']);
        $this->assertSame(
            'project_phases_2_revisedClosureAt',
            $attr['data-datepicker-target-min-date-target-value'],
        );
    }

    protected function getExtensions(): array
    {
        $this->security
            ->expects($this->once())
            ->method('isGranted')
            ->with('FEATURE_MIS_PROJECT_CIO_EDIT')
            ->willReturn(true);

        $type = new ProjectPhaseType($this->stimulusHelper, $this->security);
        $datePicker = new DatePickerType($this->translator, $this->transformer, $this->stimulusHelper);

        return [
            new PreloadedExtension([
                $type,
                $datePicker,
            ], []),
        ];
    }
}
