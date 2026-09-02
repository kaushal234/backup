<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints\AI;

use App\Entity\AI\AILog;
use App\Entity\Directory\People;
use App\Repository\AI\AILogRepository;
use App\Validator\Constraints\AI\MaxPinnedAILogs;
use App\Validator\Constraints\AI\MaxPinnedAILogsValidator;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class MaxPinnedAILogsValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    public function testIgnoresNonAILogValue(): void
    {
        $repository = $this->prophesize(AILogRepository::class);
        $repository->countPinnedByPeople()->shouldNotBeCalled();

        $this->validator->validate(new \stdClass(), new MaxPinnedAILogs());

        $this->assertNoViolation();
    }

    public function testNoViolationWhenNotPinned(): void
    {
        $repository = $this->prophesize(AILogRepository::class);
        $repository->countPinnedByPeople()->shouldNotBeCalled();

        $this->validator->validate($this->createLog(pinned: false), new MaxPinnedAILogs());

        $this->assertNoViolation();
    }

    public function testNoViolationWhenUnderLimit(): void
    {
        $log = $this->createLog(pinned: true);

        $repository = $this->prophesize(AILogRepository::class);
        $repository->countPinnedByPeople($log->people, 1)->willReturn(14);

        $this->validator = $this->createValidatorWithRepository($repository->reveal());
        $this->validator->initialize($this->context);

        $this->validator->validate($log, new MaxPinnedAILogs());

        $this->assertNoViolation();
    }

    public function testViolationWhenLimitReached(): void
    {
        $log = $this->createLog(pinned: true);

        $repository = $this->prophesize(AILogRepository::class);
        $repository->countPinnedByPeople($log->people, 1)->willReturn(15);

        $this->validator = $this->createValidatorWithRepository($repository->reveal());
        $this->validator->initialize($this->context);

        $this->validator->validate($log, new MaxPinnedAILogs());

        $this->buildViolation('You cannot pin more than {{ max }} conversations.')
            ->setParameter('{{ max }}', '15')
            ->assertRaised();
    }

    public function testViolationWhenLimitExceeded(): void
    {
        $log = $this->createLog(pinned: true);

        $repository = $this->prophesize(AILogRepository::class);
        $repository->countPinnedByPeople($log->people, 1)->willReturn(20);

        $this->validator = $this->createValidatorWithRepository($repository->reveal());
        $this->validator->initialize($this->context);

        $this->validator->validate($log, new MaxPinnedAILogs());

        $this->buildViolation('You cannot pin more than {{ max }} conversations.')
            ->setParameter('{{ max }}', '15')
            ->assertRaised();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        $repository = $this->prophesize(AILogRepository::class);

        return $this->createValidatorWithRepository($repository->reveal());
    }

    private function createLog(bool $pinned, int $id = 1): AILog
    {
        $log = new AILog();
        $log->pinned = $pinned;
        $log->people = new People();

        $reflection = new \ReflectionProperty(AILog::class, 'id');
        $reflection->setValue($log, $id);

        return $log;
    }

    private function createValidatorWithRepository(AILogRepository $repository): MaxPinnedAILogsValidator
    {
        return new MaxPinnedAILogsValidator($repository);
    }
}
