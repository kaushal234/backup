<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\Form\Type\Common;

use AppBundle\Form\Type\Common\AutocompleteChoiceType;
use AppBundle\Form\Type\Common\SelectFormType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\StimulusBundle\Helper\StimulusHelper;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class AutocompleteChoiceTypeTest extends TypeTestCase
{
    private MockObject|TranslatorInterface $translator;
    private Environment $twig;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->twig = new Environment(new ArrayLoader([
            'autocomplete.html.twig' => '{{ foo }}',
            'autocomplete2.html.twig' => '{{ bar }}',
            'autocomplete3.html.twig' => '{{ foo }}{{ bar }}',
        ]), [
            'cache' => false,
            'debug' => true,
        ]);

        parent::setUp();
    }

    public function testParent(): void
    {
        $type = new AutocompleteChoiceType($this->twig, $this->translator, new StimulusHelper($this->twig));

        $this->assertSame(SelectFormType::class, $type->getParent());
    }

    public function testWithNoOptions(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "uri" is missing.');

        $this->factory->create(AutocompleteChoiceType::class);
    }

    public function testWithMissingOptions(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "template" or "text_key" is missing.');

        $this->factory->create(AutocompleteChoiceType::class, null, [
            'uri' => 'people',
        ]);
    }

    public function testWithTagsOptions(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('Tags is not supported.');

        $this->factory->create(AutocompleteChoiceType::class, null, [
            'uri' => 'people',
            'text_key' => 'firstname',
            'tags' => true,
        ]);
    }

    public function testSimpleValidFormType(): void
    {
        $this->translator->expects($this->atLeastOnce())->method('trans')->willReturn('Placeholder translated');

        $formView = $this->factory->create(AutocompleteChoiceType::class, null, [
            'uri' => 'people',
            'text_key' => 'firstname',
        ])->createView();

        $this->assertSame('people', $formView->vars['uri']);
        $this->assertSame('firstname', $formView->vars['textKey']);
        $this->assertSame('Placeholder translated', $formView->vars['placeholder']);
    }

    public function testKeysPathAndDataToChoice(): void
    {
        $data = [
            '@id' => '/people/42',
            'businessUnit' => [
                'name' => 'BU Name',
            ],
        ];
        $formView = $this->factory->create(AutocompleteChoiceType::class, $data, [
            'uri' => 'people',
            'text_key' => '[businessUnit][name]',
        ])->createView();

        $this->assertSame('@id', $formView->vars['idKey']);
        $this->assertSame('businessUnit.name', $formView->vars['textKey']);
        $this->assertSame('BU Name', $formView->vars['choices'][0]->label);
        $this->assertSame('/people/42', $formView->vars['choices'][0]->value);
        $this->assertSame('/people/42', $formView->vars['choices'][0]->data);
        $this->assertSame(['selected' => true], $formView->vars['choices'][0]->attr);
    }

    public function testWithMultipleAndTemplate(): void
    {
        $data = [
            ['@id' => '/people/42', 'name' => 'Pierre'],
            ['@id' => '/people/43', 'name' => 'Paul'],
            ['@id' => '/people/44', 'name' => 'Jacques'],
        ];
        $formView = $this->factory->create(AutocompleteChoiceType::class, $data, [
            'uri' => 'people',
            'template' => '<b>{{name}}</b>',
            'multiple' => true,
        ])->createView();

        $this->assertCount(3, $formView->vars['choices']);
        $this->assertSame('<b>Paul</b>', $formView->vars['choices'][1]->label);
    }

    public function testWithUnresolvedName(): void
    {
        $data = '/people/42';
        $formView = $this->factory->create(AutocompleteChoiceType::class, $data, [
            'uri' => 'people',
            'template' => '<b>{{name}}</b>',
        ])->createView();

        $this->assertEmpty($formView->vars['choices'][0]->label);
        $this->assertSame('/people/42', $formView->vars['choices'][0]->value);
        $this->assertSame('/people/42', $formView->vars['choices'][0]->data);
        $this->assertSame(['selected' => true, 'data-name-unresolved' => true], $formView->vars['choices'][0]->attr);
    }

    /**
     * @dataProvider providerTemplateOptionsExceptions
     */
    public function testExceptionsWithNormalizerTemplate(string $exceptionMessage, string $template, ?string $jsTemplateSelection, ?string $jsTemplateResult): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->factory->create(AutocompleteChoiceType::class, null, [
            'uri' => 'people',
            'template' => $template,
            'js_template_selection' => $jsTemplateSelection,
            'js_template_result' => $jsTemplateResult,
        ]);
    }

    public function providerTemplateOptionsExceptions(): \Generator
    {
        yield ['Twig template "not_found.html.twig" doesn\'t exist in "template" option.', 'not_found.html.twig', null, null];
        yield ['Twig template "not_found.html.twig" doesn\'t exist in "js_template_selection" option.', 'autocomplete.html.twig', 'not_found.html.twig', null];
        yield ['Twig template "not_found.html.twig" doesn\'t exist in "js_template_result" option.', 'autocomplete.html.twig', 'autocomplete.html.twig', 'not_found.html.twig'];
    }

    /**
     * @dataProvider providerTemplateOptions
     */
    public function testNormalizeTemplateIsReuseOnJsTemplate(array $templates, array $templatesExpected): void
    {
        $formView = $this->factory->create(AutocompleteChoiceType::class, null, [
            'uri' => 'people',
            'template' => $templates[0],
            'js_template_selection' => $templates[1],
            'js_template_result' => $templates[2],
        ])->createView();

        $this->assertSame($templatesExpected[0], $formView->vars['jsTemplateSelection']);
        $this->assertSame($templatesExpected[1], $formView->vars['jsTemplateResult']);
    }

    public function providerTemplateOptions(): \Generator
    {
        yield [['autocomplete.html.twig', null, null], ['{{ foo }}', '{{ foo }}']];
        yield [['autocomplete.html.twig', 'autocomplete2.html.twig', null], ['{{ bar }}', '{{ bar }}']];
        yield [['autocomplete.html.twig', 'autocomplete2.html.twig', 'autocomplete3.html.twig'], ['{{ bar }}', '{{ foo }}{{ bar }}']];
        yield [['autocomplete.html.twig', null, 'autocomplete3.html.twig'], ['{{ foo }}', '{{ foo }}{{ bar }}']];
    }

    public function testResolveDisabledData(): void
    {
        $data = [
            ['@id' => '/people/42', 'name' => 'Pierre'],
            ['@id' => '/people/43', 'name' => 'Paul'],
            ['@id' => '/people/44', 'name' => 'Jacques'],
        ];
        $formView = $this->factory->create(AutocompleteChoiceType::class, null, [
            'uri' => 'people',
            'text_key' => 'name',
            'data_disabled' => $data,
        ])->createView();

        $this->assertSame('/people/42', $formView->vars['dataDisabled'][0]);
        $this->assertSame('/people/43', $formView->vars['dataDisabled'][1]);
        $this->assertSame('/people/44', $formView->vars['dataDisabled'][2]);
    }

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([
                new AutocompleteChoiceType($this->twig, $this->translator, new StimulusHelper($this->twig)),
                new SelectFormType($this->translator, new StimulusHelper($this->twig)),
            ], []),
        ];
    }
}
