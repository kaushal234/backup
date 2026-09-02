<?php

declare(strict_types=1);

namespace Tests\AppBundle\Form\Type\Quality\FirstArticleQualification;

use AppBundle\Form\Type\Quality\FirstArticleQualification\PlanLineType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Note: PlanLineType::configureOptions() does not declare an allowed type for
 * "plan_completion_editable" (unlike the two other options). A test asserting
 * the bool constraint has intentionally been left out until the production
 * code adds `setAllowedTypes('plan_completion_editable', 'bool')`.
 */
final class PlanLineTypeTest extends TypeTestCase
{
    // Type accepting both prior delivery and purchase-order requests.
    private const string IRI_FULL = '/plan_item_types/1';

    // Asymmetric type: catches any accidental swap between the two flags.
    private const string IRI_PRIOR_ONLY = '/plan_item_types/2';

    // Type with no "description" key: exercises the labelForIri() fallback.
    private const string IRI_NONE = '/plan_item_types/3';

    // IRI absent from plan_item_types: exercises the findType() null branch.
    private const string IRI_UNKNOWN = '/plan_item_types/999';

    // ---------------------------------------------------------------------
    // configureOptions()
    // ---------------------------------------------------------------------

    public function testDefaultOptionsAreRestrictive(): void
    {
        $resolver = new OptionsResolver();
        (new PlanLineType())->configureOptions($resolver);

        $options = $resolver->resolve([]);

        self::assertSame([], $options['plan_item_types']);
        self::assertFalse($options['plan_editable']);
        self::assertFalse($options['plan_completion_editable']);
    }

    public function testPlanItemTypesMustBeAnArray(): void
    {
        $resolver = new OptionsResolver();
        (new PlanLineType())->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);
        $resolver->resolve(['plan_item_types' => 'nope']);
    }

    public function testPlanEditableMustBeABool(): void
    {
        $resolver = new OptionsResolver();
        (new PlanLineType())->configureOptions($resolver);

        $this->expectException(InvalidOptionsException::class);
        $resolver->resolve(['plan_editable' => 'yes']);
    }

    // ---------------------------------------------------------------------
    // Normalisation of "type" (extractTypeIri)
    // ---------------------------------------------------------------------

    /**
     * @return iterable<string, array{mixed, string|null}>
     */
    public static function typeNormalizationProvider(): iterable
    {
        yield 'missing key' => [null, null];
        yield 'empty string' => ['', null];
        yield 'empty array' => [[], null];
        yield 'plain IRI string' => [self::IRI_FULL, self::IRI_FULL];
        yield 'API Platform object' => [['@id' => self::IRI_FULL, 'description' => 'x'], self::IRI_FULL];
        yield 'array without @id' => [['description' => 'x'], null];
    }

    /**
     * @dataProvider typeNormalizationProvider
     */
    public function testTypeIsNormalizedToAnIri(mixed $rawType, ?string $expected): void
    {
        $data = ['id' => '123', 'completionRate' => 50];
        if (null !== $rawType) {
            $data['type'] = $rawType;
        }

        $form = $this->createForm($data);

        self::assertSame($expected, $form->getData()['type']);
    }

    // ---------------------------------------------------------------------
    // New line vs existing line
    // ---------------------------------------------------------------------

    public function testNewLineExposesTypeAsAChoice(): void
    {
        $form = $this->createForm([]);

        $this->assertInnerType($form, 'type', ChoiceType::class);
        $this->assertFieldDisabled($form, 'type', false);
        self::assertNull($form->getData()['type']);
    }

    public function testNewLineChoiceIsDisabledWhenPlanIsNotEditable(): void
    {
        $form = $this->createForm([], ['plan_editable' => false]);

        $this->assertInnerType($form, 'type', ChoiceType::class);
        $this->assertFieldDisabled($form, 'type', true);
        $this->assertFieldDisabled($form, 'description', true);
        $this->assertFieldDisabled($form, 'comment', true);
    }

    public function testExistingLineExposesTypeAsHidden(): void
    {
        $form = $this->createForm(['id' => '123', 'type' => self::IRI_FULL, 'completionRate' => 50]);

        $this->assertInnerType($form, 'type', HiddenType::class);
    }

    public function testEmptyIdIsTreatedAsANewLine(): void
    {
        // empty('') === true: the front-end sending a blank id must fall into
        // the "new line" branch, which exposes type as a ChoiceType.
        // No preset type here on purpose — with a type set, checkbox state
        // would follow the type's flags, not the isNewLine fallback.
        $form = $this->createForm(['id' => '']);

        $this->assertInnerType($form, 'type', ChoiceType::class);
        $this->assertFieldDisabled($form, 'requestedPriorDelivery', false);
        $this->assertFieldDisabled($form, 'requestedAtPurchaseOrder', false);
    }

    // ---------------------------------------------------------------------
    // Checkboxes: prior vs purchase
    // ---------------------------------------------------------------------

    public function testNewLineWithoutTypeAllowsBothCheckboxes(): void
    {
        $form = $this->createForm([]);

        $this->assertFieldDisabled($form, 'requestedPriorDelivery', false);
        $this->assertFieldDisabled($form, 'requestedAtPurchaseOrder', false);
    }

    public function testCheckboxesFollowTheTypeFlagsIndependently(): void
    {
        $form = $this->createForm(['id' => '123', 'type' => self::IRI_PRIOR_ONLY, 'completionRate' => 50]);

        $this->assertFieldDisabled($form, 'requestedPriorDelivery', false);
        $this->assertFieldDisabled($form, 'requestedAtPurchaseOrder', true);
    }

    public function testTypeAllowingBothEnablesBothCheckboxes(): void
    {
        $form = $this->createForm(['id' => '123', 'type' => self::IRI_FULL, 'completionRate' => 25]);

        $this->assertFieldDisabled($form, 'requestedPriorDelivery', false);
        $this->assertFieldDisabled($form, 'requestedAtPurchaseOrder', false);
    }

    public function testTypeAllowingNeitherDisablesBothCheckboxes(): void
    {
        $form = $this->createForm(['id' => '123', 'type' => self::IRI_NONE, 'completionRate' => 25]);

        $this->assertFieldDisabled($form, 'requestedPriorDelivery', true);
        $this->assertFieldDisabled($form, 'requestedAtPurchaseOrder', true);
    }

    public function testUnknownTypeOnAnExistingLineDisablesBothCheckboxes(): void
    {
        // findType() returns null → allowed flags fall back to $isNewLine (false here).
        $form = $this->createForm(['id' => '123', 'type' => self::IRI_UNKNOWN, 'completionRate' => 50]);

        $this->assertFieldDisabled($form, 'description', false);
        $this->assertFieldDisabled($form, 'requestedPriorDelivery', true);
        $this->assertFieldDisabled($form, 'requestedAtPurchaseOrder', true);
    }

    public function testCheckboxesStayDisabledWhenPlanIsNotEditableEvenIfTypeAllowsThem(): void
    {
        $form = $this->createForm(
            ['id' => '123', 'type' => self::IRI_FULL, 'completionRate' => 50],
            ['plan_editable' => false],
        );

        $this->assertFieldDisabled($form, 'requestedPriorDelivery', true);
        $this->assertFieldDisabled($form, 'requestedAtPurchaseOrder', true);
    }

    // ---------------------------------------------------------------------
    // completionRate / locking
    // ---------------------------------------------------------------------

    public function testCompletedLineLocksEverythingIncludingCompletionRate(): void
    {
        $form = $this->createForm(['id' => '123', 'type' => self::IRI_FULL, 'completionRate' => 100]);

        $this->assertFieldDisabled($form, 'description', true);
        $this->assertFieldDisabled($form, 'comment', true);
        $this->assertFieldDisabled($form, 'requestedPriorDelivery', true);
        $this->assertFieldDisabled($form, 'requestedAtPurchaseOrder', true);
        $this->assertFieldDisabled($form, 'completionRate', true);
    }

    public function testCompletionRateAsStringIsAlsoDetectedAsCompleted(): void
    {
        // (int) '100' === COMPLETED_RATE: covers payloads where the rate arrives as a string.
        $form = $this->createForm(['id' => '123', 'type' => self::IRI_FULL, 'completionRate' => '100']);

        $this->assertFieldDisabled($form, 'description', true);
        $this->assertFieldDisabled($form, 'completionRate', true);
    }

    public function testUncompletedLineKeepsCompletionRateEditable(): void
    {
        $form = $this->createForm(['id' => '123', 'type' => self::IRI_FULL, 'completionRate' => 75]);

        $this->assertFieldDisabled($form, 'completionRate', false);
    }

    public function testCompletionRateCanBeEditableWhileTheLineIsLocked(): void
    {
        $form = $this->createForm(
            ['id' => '123', 'type' => self::IRI_FULL, 'completionRate' => 50],
            ['plan_editable' => false, 'plan_completion_editable' => true],
        );

        $this->assertFieldDisabled($form, 'description', true);
        $this->assertFieldDisabled($form, 'completionRate', false);
    }

    public function testLineCanBeEditableWhileCompletionRateIsLocked(): void
    {
        $form = $this->createForm(
            ['id' => '123', 'type' => self::IRI_FULL, 'completionRate' => 50],
            ['plan_editable' => true, 'plan_completion_editable' => false],
        );

        $this->assertFieldDisabled($form, 'description', false);
        $this->assertFieldDisabled($form, 'completionRate', true);
    }

    public function testCompletionRateOffersTheExpectedChoices(): void
    {
        $form = $this->createForm(['id' => '123', 'type' => self::IRI_FULL, 'completionRate' => 0]);

        $choices = $form->get('completionRate')->getConfig()->getOption('choices');

        self::assertSame(['0 %' => 0, '25 %' => 25, '50 %' => 50, '75 %' => 75, '100 %' => 100], $choices);
    }

    // ---------------------------------------------------------------------
    // Type choice labels
    // ---------------------------------------------------------------------

    public function testTypeChoicesAreLabelledByDescriptionWithIriFallback(): void
    {
        $form = $this->createForm([]);
        $view = $form->createView();

        $labels = array_map(
            static fn ($choiceView) => $choiceView->label,
            $view->children['type']->vars['choices'],
        );

        // IRI_NONE has no "description", so labelForIri() falls back to the IRI itself.
        self::assertSame(['Contrôle dimensionnel', 'Rapport matière', self::IRI_NONE], $labels);
        self::assertSame('—', $view->children['type']->vars['placeholder']);
    }

    // ---------------------------------------------------------------------
    // Submission
    // ---------------------------------------------------------------------

    public function testSubmitNewLine(): void
    {
        $form = $this->createForm([]);

        $form->submit([
            'id' => '',
            'type' => self::IRI_FULL,
            'description' => '',
            'comment' => '',
            'requestedPriorDelivery' => '1',
            'requestedAtPurchaseOrder' => null,
            'completionRate' => '25',
        ]);

        self::assertTrue($form->isSynchronized());

        $data = $form->getData();
        self::assertSame(self::IRI_FULL, $data['type']);
        // description declares empty_data => '' so a blank submission stays ''.
        self::assertSame('', $data['description']);
        // comment has no empty_data: a blank string is normalised to null.
        self::assertNull($data['comment']);
        self::assertTrue($data['requestedPriorDelivery']);
        self::assertFalse($data['requestedAtPurchaseOrder']);
        // ChoiceType with int choices must reverse-transform back to an int.
        self::assertSame(25, $data['completionRate']);
    }

    public function testSubmitAnUnknownTypeIsNotSynchronized(): void
    {
        // Guards against duplicated plan lines carrying a now-obsolete type IRI:
        // ChoiceType must reject any value outside plan_item_types.
        $form = $this->createForm([]);

        $form->submit([
            'type' => self::IRI_UNKNOWN,
            'description' => 'x',
            'comment' => '',
            'completionRate' => '0',
        ]);

        self::assertFalse($form->get('type')->isSynchronized());
    }

    public function testSubmitIgnoresDisabledFieldsOnACompletedLine(): void
    {
        $initial = [
            'id' => '123',
            'type' => self::IRI_FULL,
            'description' => 'initial description',
            'comment' => 'initial comment',
            'requestedPriorDelivery' => false,
            'requestedAtPurchaseOrder' => false,
            'completionRate' => 100,
        ];

        $form = $this->createForm($initial);
        $form->submit([
            'id' => '123',
            'type' => self::IRI_FULL,
            'description' => 'tampered description',
            'comment' => 'tampered comment',
            'requestedPriorDelivery' => '1',
            'requestedAtPurchaseOrder' => '1',
            'completionRate' => '0',
        ]);

        self::assertTrue($form->isSynchronized());

        // Disabled fields must keep their initial value regardless of the payload.
        $data = $form->getData();
        self::assertSame('initial description', $data['description']);
        self::assertSame('initial comment', $data['comment']);
        self::assertFalse($data['requestedPriorDelivery']);
        self::assertFalse($data['requestedAtPurchaseOrder']);
        self::assertSame(100, $data['completionRate']);
    }

    public function testSubmitIgnoresACheckboxDisabledByItsType(): void
    {
        $initial = [
            'id' => '123',
            'type' => self::IRI_PRIOR_ONLY,
            'description' => 'desc',
            'comment' => null,
            'requestedPriorDelivery' => false,
            'requestedAtPurchaseOrder' => false,
            'completionRate' => 50,
        ];

        $form = $this->createForm($initial);
        $form->submit([
            'id' => '123',
            'type' => self::IRI_PRIOR_ONLY,
            'description' => 'desc',
            'comment' => '',
            'requestedPriorDelivery' => '1',
            // Disabled by IRI_PRIOR_ONLY (requestableAtPurchaseOrder => false):
            // must keep the initial value even if the payload tries to flip it.
            'requestedAtPurchaseOrder' => '1',
            'completionRate' => '75',
        ]);

        $data = $form->getData();
        self::assertTrue($data['requestedPriorDelivery']);
        self::assertFalse($data['requestedAtPurchaseOrder']);
        self::assertSame(75, $data['completionRate']);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function itemTypes(): array
    {
        return [
            [
                '@id' => self::IRI_FULL,
                'description' => 'Contrôle dimensionnel',
                'requestablePriorDelivery' => true,
                'requestableAtPurchaseOrder' => true,
            ],
            [
                '@id' => self::IRI_PRIOR_ONLY,
                'description' => 'Rapport matière',
                'requestablePriorDelivery' => true,
                'requestableAtPurchaseOrder' => false,
            ],
            [
                '@id' => self::IRI_NONE,
                'requestablePriorDelivery' => false,
                'requestableAtPurchaseOrder' => false,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     */
    private function createForm(array $data = [], array $options = []): FormInterface
    {
        return $this->factory->create(PlanLineType::class, $data, $options + [
            'plan_item_types' => self::itemTypes(),
            'plan_editable' => true,
            'plan_completion_editable' => true,
        ]);
    }

    private function assertFieldDisabled(FormInterface $form, string $child, bool $expected): void
    {
        self::assertSame(
            $expected,
            $form->get($child)->getConfig()->getDisabled(),
            \sprintf('Field "%s" should be %s.', $child, $expected ? 'disabled' : 'enabled'),
        );
    }

    private function assertInnerType(FormInterface $form, string $child, string $expectedClass): void
    {
        self::assertInstanceOf($expectedClass, $form->get($child)->getConfig()->getType()->getInnerType());
    }
}
