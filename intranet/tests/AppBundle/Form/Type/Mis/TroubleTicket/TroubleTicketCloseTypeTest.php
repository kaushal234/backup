<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Unit test for {@see TroubleTicketCloseType::validateSatisfaction()}.
 *
 * This is the server-side port of the old React rule (validationClose.ts): a satisfaction
 * rating is mandatory only when the chosen status is SOLVED. The callback is pure enough to
 * exercise in isolation with a mocked execution context, so no form factory is required here;
 * full form-building and rendering behaviour is covered by the live component test instead.
 */
class TroubleTicketCloseTypeTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;

    private TroubleTicketCloseType $formType;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->formType = new TroubleTicketCloseType($this->translator);
    }

    public function testSatisfactionChoicesConstantIsStable(): void
    {
        // These values are persisted / sent to the API, so guard them against accidental edits.
        self::assertSame([
            'Not satisfied at all' => 'Not satisfied at all',
            'Not much satisfied' => 'Not much satisfied',
            'Satisfied' => 'Satisfied',
            'Very satisfied' => 'Very satisfied',
        ], TroubleTicketCloseType::SATISFACTION_CHOICES);
    }

    public function testNullDataAddsNoViolation(): void
    {
        $this->translator->expects(self::never())->method('trans');

        $this->formType->validateSatisfaction(null, $this->contextExpectingNoViolation());
    }

    public function testNonSolvedStatusWithoutSatisfactionAddsNoViolation(): void
    {
        $this->translator->expects(self::never())->method('trans');

        $this->formType->validateSatisfaction(
            ['status' => 'NOT AN ISSUE', 'satisfaction' => null],
            $this->contextExpectingNoViolation(),
        );
    }

    public function testMissingStatusAddsNoViolation(): void
    {
        $this->translator->expects(self::never())->method('trans');

        $this->formType->validateSatisfaction(
            ['satisfaction' => null],
            $this->contextExpectingNoViolation(),
        );
    }

    public function testSolvedStatusWithSatisfactionAddsNoViolation(): void
    {
        $this->translator->expects(self::never())->method('trans');

        $this->formType->validateSatisfaction(
            ['status' => 'SOLVED', 'satisfaction' => 'Satisfied'],
            $this->contextExpectingNoViolation(),
        );
    }

    public function testSolvedStatusWithEmptySatisfactionAddsViolationOnSatisfactionPath(): void
    {
        $this->translator
            ->expects(self::once())
            ->method('trans')
            ->with('trouble_ticket.errors.satisfaction', [], 'trouble_ticket')
            ->willReturn('Satisfaction is required.');

        $this->formType->validateSatisfaction(
            ['status' => 'SOLVED', 'satisfaction' => ''],
            $this->contextExpectingViolation('Satisfaction is required.'),
        );
    }

    public function testSolvedStatusWithMissingSatisfactionKeyAddsViolation(): void
    {
        $this->translator
            ->expects(self::once())
            ->method('trans')
            ->willReturn('Satisfaction is required.');

        $this->formType->validateSatisfaction(
            ['status' => 'SOLVED'],
            $this->contextExpectingViolation('Satisfaction is required.'),
        );
    }

    private function contextExpectingNoViolation(): ExecutionContextInterface&MockObject
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects(self::never())->method('buildViolation');

        return $context;
    }

    private function contextExpectingViolation(string $message): ExecutionContextInterface&MockObject
    {
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $builder->expects(self::once())
            ->method('atPath')
            ->with('satisfaction')
            ->willReturnSelf();
        $builder->expects(self::once())->method('addViolation');

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects(self::once())
            ->method('buildViolation')
            ->with($message)
            ->willReturn($builder);

        return $context;
    }
}
