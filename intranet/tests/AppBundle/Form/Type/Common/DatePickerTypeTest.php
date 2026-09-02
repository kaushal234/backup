<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use AppBundle\Form\DataTransformer\DatePickerModelTransformer;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\StimulusBundle\Helper\StimulusHelper;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class DatePickerTypeTest extends TypeTestCase
{
    private MockObject&TranslatorInterface $translator;

    private StimulusHelper $stimulusHelper;

    private DatePickerModelTransformer $transformer;

    protected function setUp(): void
    {
        $twig = new Environment(new ArrayLoader(), ['cache' => false, 'debug' => true]);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturn('MM/dd/yyyy');
        $this->stimulusHelper = new StimulusHelper($twig);
        $this->transformer = new DatePickerModelTransformer();

        parent::setUp();
    }

    /**
     * Guards the regression where min/max string restrictions produced an inverted
     * (empty) range, leaving every date disabled in the picker (TTS#40226).
     */
    public function testSignedStringRestrictionsProduceAValidRange(): void
    {
        $view = $this->factory
            ->create(DatePickerType::class, null, [
                'restrictions' => [
                    'minDateStr' => '-2 years',
                    'maxDateStr' => 'now',
                ],
            ])
            ->createView()
        ;

        $minDate = new \DateTime($view->vars['options']['restrictions']['minDate']);
        $maxDate = new \DateTime($view->vars['options']['restrictions']['maxDate']);

        // The lower bound must stay before the upper bound, otherwise no date is selectable.
        $this->assertLessThan($maxDate, $minDate);

        // minDateStr '-2 years' must resolve to roughly two years before now.
        $expectedMin = (new \DateTime())->modify('-2 years');
        $this->assertLessThanOrEqual(
            86400,
            abs($expectedMin->getTimestamp() - $minDate->getTimestamp()),
        );
    }

    protected function getExtensions(): array
    {
        $datePicker = new DatePickerType($this->translator, $this->transformer, $this->stimulusHelper);

        return [
            new PreloadedExtension([$datePicker], []),
        ];
    }
}
