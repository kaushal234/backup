<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\StimulusBundle\Helper\StimulusHelper;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class SelectFormTypeTest extends TypeTestCase
{
    private MockObject|TranslatorInterface $translator;

    private Environment $twig;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->twig = new Environment(new ArrayLoader([]), [
            'cache' => false,
            'debug' => true,
        ]);

        parent::setUp();
    }

    public function testParent(): void
    {
        $type = new SelectFormType($this->translator, new StimulusHelper($this->twig));

        $this->assertSame(ChoiceType::class, $type->getParent());
    }

    public function testWithTagsOptions(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('Tags is not supported.');

        $this->factory->create(SelectFormType::class, null, [
            'tags' => true,
        ]);
    }

    public function testSimpleValidFormType(): void
    {
        $this->translator->expects($this->once())->method('trans')->willReturn('Placeholder translated');

        $formView = $this->factory->create(SelectFormType::class)->createView();

        $this->assertSame('Placeholder translated', $formView->vars['placeholder']);
    }

    protected function getExtensions(): array
    {
        $type = new SelectFormType($this->translator, new StimulusHelper($this->twig));

        return [
            new PreloadedExtension([$type], []),
        ];
    }
}
