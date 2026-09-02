<?php

declare(strict_types=1);

namespace App\Tests\Manager\Quality\FirstArticleQualification;

use App\Entity\Directory\Location;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\PlanItem;
use App\Entity\Quality\FirstArticleQualification\PlanItemType;
use App\Exception\FirstArticleQualificationPlanDuplicationException as DuplicationFailure;
use App\Manager\Quality\FirstArticleQualification\QualificationPlanDuplicatorManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

final class QualificationPlanDuplicatorTest extends TestCase
{
    use ProphecyTrait;

    private Location $factoryA;
    private Location $factoryB;
    private PlanItemType $typeA;
    private PlanItemType $typeB;

    protected function setUp(): void
    {
        // Fresh entity instances — the duplicator compares Location and PlanItemType
        // by reference, so we just need distinct object identities, not populated data.
        $this->factoryA = new Location();
        $this->factoryB = new Location();
        $this->typeA = new PlanItemType();
        $this->typeB = new PlanItemType();
    }

    // -----------------------------------------------------------------
    // Rejections — source approval status
    // -----------------------------------------------------------------

    public function testThrowsWhenSourceIsNotYetApproved(): void
    {
        $source = $this->createFaq($this->factoryA, FirstArticleQualification::NOT_APPROVED_YET);
        $target = $this->createFaq($this->factoryA);

        try {
            $this->newDuplicatorWithNoWrite()->duplicate($source, [$target]);
            self::fail('Duplication should have been refused: source is not approved.');
        } catch (DuplicationFailure $e) {
            self::assertSame(409, $e->statusCode);
            self::assertSame([], $e->rejections, 'Approval failure is global, not per-target.');
        }
    }

    public function testThrowsWhenSourceIsUnapproved(): void
    {
        // Same 409 branch as NOT_APPROVED_YET — only APPROVED goes through.
        $source = $this->createFaq($this->factoryA, FirstArticleQualification::UNAPPROVED);
        $target = $this->createFaq($this->factoryA);

        $this->expectException(DuplicationFailure::class);

        $this->newDuplicatorWithNoWrite()->duplicate($source, [$target]);
    }

    // -----------------------------------------------------------------
    // Rejections — per-target checks
    // -----------------------------------------------------------------

    public function testRejectsTargetThatAlreadyHasAPlan(): void
    {
        $source = $this->createApprovedSourceWithOneItem();

        $targetWithPlan = $this->createFaq($this->factoryA);
        $targetWithPlan->addPlan($this->createSourceItem());

        try {
            $this->newDuplicatorWithNoWrite()->duplicate($source, [$targetWithPlan]);
            self::fail('Duplication should have been refused: target already has a plan.');
        } catch (DuplicationFailure $e) {
            self::assertSame(422, $e->statusCode);
            self::assertCount(1, $e->rejections);
            self::assertSame(DuplicationFailure::REASON_TARGET_HAS_PLAN, $e->rejections[0]['reason']);
            self::assertSame($targetWithPlan, $e->rejections[0]['faq']);
        }
    }

    public function testRejectsTargetOnAnotherFactory(): void
    {
        $source = $this->createApprovedSourceWithOneItem();
        $target = $this->createFaq($this->factoryB); // different location = different factory

        try {
            $this->newDuplicatorWithNoWrite()->duplicate($source, [$target]);
            self::fail('Duplication should have been refused: target lives on another factory.');
        } catch (DuplicationFailure $e) {
            self::assertSame(DuplicationFailure::REASON_DIFFERENT_FACTORY, $e->rejections[0]['reason']);
        }
    }

    public function testTargetHasPlanTakesPrecedenceOverDifferentFactory(): void
    {
        $source = $this->createApprovedSourceWithOneItem();

        // Fails both checks: has a plan AND lives on another factory.
        $target = $this->createFaq($this->factoryB);
        $target->addPlan($this->createSourceItem());

        try {
            $this->newDuplicatorWithNoWrite()->duplicate($source, [$target]);
            self::fail('Duplication should have been refused.');
        } catch (DuplicationFailure $e) {
            self::assertCount(1, $e->rejections, 'Exactly one reason must be reported per target.');
            self::assertSame(DuplicationFailure::REASON_TARGET_HAS_PLAN, $e->rejections[0]['reason']);
        }
    }

    public function testCollectsAllRejectionsBeforeThrowing(): void
    {
        $source = $this->createApprovedSourceWithOneItem();

        $withPlan = $this->createFaq($this->factoryA);
        $withPlan->addPlan($this->createSourceItem());
        $otherFactory = $this->createFaq($this->factoryB);
        $eligible = $this->createFaq($this->factoryA); // valid, must NOT appear in rejections

        try {
            $this->newDuplicatorWithNoWrite()->duplicate($source, [$withPlan, $otherFactory, $eligible]);
            self::fail('Duplication should have been refused.');
        } catch (DuplicationFailure $e) {
            self::assertCount(2, $e->rejections);
            self::assertSame(DuplicationFailure::REASON_TARGET_HAS_PLAN, $e->rejections[0]['reason']);
            self::assertSame(DuplicationFailure::REASON_DIFFERENT_FACTORY, $e->rejections[1]['reason']);
        }
    }

    public function testRejectsSourcePassedAsItsOwnTarget(): void
    {
        // The source is APPROVED, therefore its plan is non-empty by invariant,
        // so passing it as a target trips the "target_has_plan" check — the rule
        // is enforced implicitly, no dedicated code path.
        $source = $this->createApprovedSourceWithOneItem();

        try {
            $this->newDuplicatorWithNoWrite()->duplicate($source, [$source]);
            self::fail('Duplicating a FAQ onto itself must be refused.');
        } catch (DuplicationFailure $e) {
            self::assertSame(DuplicationFailure::REASON_TARGET_HAS_PLAN, $e->rejections[0]['reason']);
        }
    }

    // -----------------------------------------------------------------
    // Happy path — clone shape and reset fields
    // -----------------------------------------------------------------

    public function testClonesEveryPreservedFieldAndResetsTheOthersOnEachTarget(): void
    {
        $sourceItem = $this->createSourceItem(
            type: $this->typeA,
            description: 'Machined surface inspection',
            comment: 'Please double-check the roughness',
            completionRate: 100,
            priorDelivery: true,
            atPurchaseOrder: false,
        );
        $source = $this->createFaq($this->factoryA, FirstArticleQualification::APPROVED);
        $source->addPlan($sourceItem);

        $targetA = $this->createFaq($this->factoryA);
        $targetB = $this->createFaq($this->factoryA);

        $result = $this->newDuplicatorWithSingleFlush()->duplicate($source, [$targetA, $targetB]);

        self::assertSame([$targetA, $targetB], $result);

        foreach ([$targetA, $targetB] as $target) {
            self::assertCount(1, $target->getPlan(), 'Each target receives one clone per source item.');
            $clone = $target->getPlan()->first();
            self::assertNotSame($sourceItem, $clone, 'The clone must be a brand-new PlanItem instance.');

            // Preserved.
            self::assertSame($this->typeA, $clone->getType(), 'PlanItemType is shared by reference.');
            self::assertSame('Machined surface inspection', $clone->getDescription());
            self::assertSame('Please double-check the roughness', $clone->getComment());
            self::assertTrue($clone->isRequestedPriorDelivery());
            self::assertFalse($clone->isRequestedAtPurchaseOrder());

            // Reset.
            self::assertSame(0, $clone->getCompletionRate());
            self::assertNull($clone->getValidatedAt());
            self::assertNull($clone->getValidatedBy());

            // Reverse side wired via FirstArticleQualification::addPlan().
            self::assertSame($target, $clone->getFirstArticleQualification());
        }

        // Source is untouched.
        self::assertCount(1, $source->getPlan());
        self::assertSame(100, $source->getPlan()->first()->getCompletionRate());
    }

    public function testResetsPlanDefinitionCompletedAtOnEachTarget(): void
    {
        $source = $this->createApprovedSourceWithOneItem();

        $target = $this->createFaq($this->factoryA);
        $target->setPlanDefinitionCompletedAt(new \DateTimeImmutable('2024-01-15'));

        $this->newDuplicatorWithSingleFlush()->duplicate($source, [$target]);

        self::assertNull($target->getPlanDefinitionCompletedAt());
    }

    public function testClonesEveryItemOfAMultiItemSourcePlan(): void
    {
        $source = $this->createFaq($this->factoryA, FirstArticleQualification::APPROVED);
        $source->addPlan($this->createSourceItem(type: $this->typeA, description: 'first'));
        $source->addPlan($this->createSourceItem(type: $this->typeB, description: 'second'));
        $source->addPlan($this->createSourceItem(type: $this->typeA, description: 'third'));

        $target = $this->createFaq($this->factoryA);

        $this->newDuplicatorWithSingleFlush()->duplicate($source, [$target]);

        self::assertCount(3, $target->getPlan());
        $descriptions = array_map(
            static fn (PlanItem $item) => $item->getDescription(),
            $target->getPlan()->toArray(),
        );
        self::assertSame(['first', 'second', 'third'], $descriptions);
    }

    public function testDeduplicatesTargetsPassedMultipleTimes(): void
    {
        // The API processor should never send doubles in practice, but the manager
        // guards against a rogue payload double-cloning into the same target.
        $source = $this->createApprovedSourceWithOneItem();
        $target = $this->createFaq($this->factoryA);

        $result = $this->newDuplicatorWithSingleFlush()->duplicate($source, [$target, $target, $target]);

        self::assertSame([$target], $result);
        self::assertCount(1, $target->getPlan());
    }

    // -----------------------------------------------------------------
    // Transactional guarantees
    // -----------------------------------------------------------------

    public function testDoesNotWriteAnythingWhenAtLeastOneTargetIsRejected(): void
    {
        // The Prophecy on wrapInTransaction/flush enforces "no write" —
        // any DB access would fail the shouldNotBeCalled() promises below.
        $source = $this->createApprovedSourceWithOneItem();
        $eligible = $this->createFaq($this->factoryA);
        $rejected = $this->createFaq($this->factoryB);

        try {
            $this->newDuplicatorWithNoWrite()->duplicate($source, [$eligible, $rejected]);
            self::fail('Duplication should have been refused because of the second target.');
        } catch (DuplicationFailure) {
            // Prophecies validated on tearDown.
        }

        self::assertTrue($eligible->getPlan()->isEmpty(), 'The eligible target must not receive clones.');
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    private function createFaq(
        Location $location,
        string $status = FirstArticleQualification::NOT_APPROVED_YET,
    ): FirstArticleQualification {
        $faq = new FirstArticleQualification();
        $faq->setLocation($location);
        $faq->setPlanApprovalStatus($status);

        return $faq;
    }

    private function createApprovedSourceWithOneItem(): FirstArticleQualification
    {
        $source = $this->createFaq($this->factoryA, FirstArticleQualification::APPROVED);
        $source->addPlan($this->createSourceItem());

        return $source;
    }

    private function createSourceItem(
        ?PlanItemType $type = null,
        string $description = 'desc',
        ?string $comment = 'comment',
        int $completionRate = 75,
        ?bool $priorDelivery = true,
        ?bool $atPurchaseOrder = false,
    ): PlanItem {
        $item = new PlanItem();
        $item->setType($type ?? $this->typeA);
        $item->setDescription($description);
        $item->setComment($comment);
        $item->setCompletionRate($completionRate);
        $item->setRequestedPriorDelivery($priorDelivery);
        $item->setRequestedAtPurchaseOrder($atPurchaseOrder);

        return $item;
    }

    /**
     * Prophecy configured to fail if the duplicator tries to persist anything.
     * Used for every rejection scenario.
     */
    private function newDuplicatorWithNoWrite(): QualificationPlanDuplicatorManager
    {
        $em = $this->prophesize(EntityManagerInterface::class);
        $em->wrapInTransaction(Argument::type('callable'))->shouldNotBeCalled();
        $em->flush()->shouldNotBeCalled();

        return new QualificationPlanDuplicatorManager($em->reveal());
    }

    /**
     * Prophecy configured for a happy-path run: the transaction wrapper is
     * invoked exactly once, its callable is executed, and a single flush is
     * expected. Any deviation (double flush, missing transaction) fails the test.
     */
    private function newDuplicatorWithSingleFlush(): QualificationPlanDuplicatorManager
    {
        $em = $this->prophesize(EntityManagerInterface::class);
        $em->wrapInTransaction(Argument::type('callable'))
            ->shouldBeCalledOnce()
            ->will(static fn (array $args) => $args[0]());
        $em->flush()->shouldBeCalledOnce();

        return new QualificationPlanDuplicatorManager($em->reveal());
    }
}
