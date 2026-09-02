<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\Form\Type\Mis\Project;

use AppBundle\Form\DataTransformer\DatePickerModelTransformer;
use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Mis\Project\ProjectType;
use AppBundle\Form\Type\TextAreaEditorType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\StimulusBundle\Helper\StimulusHelper;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class ProjectTypeTest extends TypeTestCase
{
    private MockObject&TranslatorInterface $translator;

    private Environment $twig;

    private StimulusHelper $stimulusHelper;

    private DatePickerModelTransformer $transformer;

    protected function setUp(): void
    {
        $this->twig = new Environment(new ArrayLoader([
            'directory/people/partial/_search.html.twig' => '',
        ]), ['cache' => false, 'debug' => true]);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->stimulusHelper = new StimulusHelper($this->twig);
        $this->transformer = new DatePickerModelTransformer();

        parent::setUp();
    }

    public function testConfigureOptions(): void
    {
        $formType = new ProjectType();
        $resolver = new OptionsResolver();
        $formType->configureOptions($resolver);
        $options = $resolver->resolve();
        $this->assertSame('mis_project', $options['translation_domain']);
    }

    public function testEmptyForm(): void
    {
        $form = $this->factory->create(ProjectType::class);
        $phasesOptions = $form->get('phases')->getConfig()->getOption('entry_options');

        $this->assertTrue($phasesOptions['is_new']);
        $this->assertNull($phasesOptions['active_phase']);
    }

    public function testFormWithData(): void
    {
        $form = $this->factory->create(ProjectType::class, [
            '@id' => '/projects/1',
            'activePhase' => ['number' => 3],
        ]);
        $phasesOptions = $form->get('phases')->getConfig()->getOption('entry_options');

        $this->assertFalse($phasesOptions['is_new']);
        $this->assertSame(3, $phasesOptions['active_phase']);
    }

    protected function getExtensions(): array
    {
        $type = new ProjectType();
        $autocomplete = new AutocompleteChoiceType($this->twig, $this->translator, $this->stimulusHelper);
        $selectForm = new SelectFormType($this->translator, $this->stimulusHelper);
        $textArea = new TextAreaEditorType($this->stimulusHelper);
        $datePicker = new DatePickerType($this->translator, $this->transformer, $this->stimulusHelper);

        return [
            new PreloadedExtension([
                $type,
                $autocomplete,
                $selectForm,
                $textArea,
                $datePicker,
            ], []),
        ];
    }
}
