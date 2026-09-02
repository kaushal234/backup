<?php

declare(strict_types=1);

namespace App\Tests\Notifier\Service;

use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Product;
use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\Builders\TocUpdatedEmailBuilder;
use App\Notifier\Service\TechnicianOnCall\RecipientsFinder;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;
use App\Repository\Sales\CustomerRelationshipTeamRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TocUpdatedEmailBuilderTest extends KernelTestCase
{
    use ProphecyTrait;

    private TocUpdatedEmailBuilder $builder;

    private TranslatorInterface|ObjectProphecy $translator;

    private ObjectProphecy $recipientsFinder;

    private TechnicianOnCall $toc;

    protected function setUp(): void
    {
        $ssoProphecy = $this->prophesize(Location::class);
        $ssoProphecy->getName()->willReturn('Sales Org');

        $productProphecy = $this->prophesize(Product::class);
        $productProphecy->getName()->willReturn('Taupe Model');

        $equipmentRecordProphecy = $this->prophesize(EquipmentRecord::class);
        $equipmentRecordProphecy->getSalesOrganisation()->willReturn($ssoProphecy->reveal());
        $equipmentRecordProphecy->getProduct()->willReturn($productProphecy->reveal());

        $customerProphecy = $this->prophesize(Customer::class);
        $customerProphecy->getName()->willReturn('Customer De la Tranquillité');

        $tocProphecy = $this->prophesize(TechnicianOnCall::class);
        $tocProphecy->getId()->willReturn(156337);
        $tocProphecy->getOpenDays()->willReturn(1);

        $this->toc = $tocProphecy->reveal();
        $this->toc->equipmentRecord = $equipmentRecordProphecy->reveal();
        $this->toc->customer = $customerProphecy->reveal();

        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->translator
            ->trans(Argument::any(), Argument::any(), Argument::any())
            ->willReturn('TOC#156337 has been updated');

        $this->recipientsFinder = $this->prophesize(RecipientsFinder::class);
        $this->recipientsFinder
            ->findTos(Argument::any(), Argument::any(), Argument::any())
            ->willReturn(['someone@example.com']);
        $this->recipientsFinder
            ->findCcs(Argument::any(), Argument::any(), Argument::any())
            ->willReturn([]);

        $security = $this->prophesize(Security::class);
        $security->getUser()->willReturn(null);

        $crtRepository = $this->prophesize(CustomerRelationshipTeamRepository::class);
        $normalizer = $this->prophesize(NormalizerInterface::class);

        $this->builder = new TocUpdatedEmailBuilder();
        $this->builder->setDependencies(
            $this->translator->reveal(),
            $this->recipientsFinder->reveal(),
            $security->reveal(),
            $crtRepository->reveal(),
            $normalizer->reveal(),
        );
    }

    public function testSupports(): void
    {
        self::assertTrue($this->builder->supports(TechnicianOnCallMailSubject::TOC_UPDATED));
        self::assertFalse($this->builder->supports(TechnicianOnCallMailSubject::TOC_SOLVED_EXTERNAL));
    }

    public function testNewDocumentationFieldIsExtractedFromChangeSet(): void
    {
        $context = [
            'changeSet' => [
                'status' => ['IN_PROGRESS', 'SOLVED'],
                'symptoms' => ['', 'New symptoms text'],
                'originalSymptoms' => ['', 'New symptoms text'],
            ],
        ];

        $email = $this->builder->build(TechnicianOnCallMailSubject::TOC_UPDATED, $this->toc, $context);
        $resultContext = $email->getContext();

        self::assertSame(['symptoms'], $resultContext['documentationFields']);
        self::assertArrayHasKey('status', $resultContext['changeSet']);
        self::assertArrayNotHasKey('symptoms', $resultContext['changeSet']);
        self::assertArrayNotHasKey('originalSymptoms', $resultContext['changeSet']);
    }

    public function testDocumentationFieldClearedOnReopenIsNotShownAsDocumentationBox(): void
    {
        $context = [
            'changeSet' => [
                'status' => ['SOLVED', 'IN_PROGRESS'],
                'symptoms' => ['Old symptoms text', null],
                'originalSymptoms' => ['Old symptoms text', null],
                'rootCause' => ['Old root cause', ''],
                'originalRootCause' => ['Old root cause', ''],
            ],
        ];

        $email = $this->builder->build(TechnicianOnCallMailSubject::TOC_UPDATED, $this->toc, $context);
        $resultContext = $email->getContext();

        self::assertSame([], $resultContext['documentationFields']);
        self::assertArrayHasKey('status', $resultContext['changeSet']);
        self::assertArrayNotHasKey('symptoms', $resultContext['changeSet']);
        self::assertArrayNotHasKey('originalSymptoms', $resultContext['changeSet']);
        self::assertArrayNotHasKey('rootCause', $resultContext['changeSet']);
        self::assertArrayNotHasKey('originalRootCause', $resultContext['changeSet']);
    }

    public function testOrdinaryFieldChangeWithNoDocumentationFieldsLeavesChangeSetIntact(): void
    {
        $context = [
            'changeSet' => [
                'status' => ['IN_PROGRESS', 'SOLVED'],
                'solvedAt' => ['', '2026-08-28'],
            ],
        ];

        $email = $this->builder->build(TechnicianOnCallMailSubject::TOC_UPDATED, $this->toc, $context);
        $resultContext = $email->getContext();

        self::assertSame([], $resultContext['documentationFields']);
        self::assertSame(['IN_PROGRESS', 'SOLVED'], $resultContext['changeSet']['status']);
        self::assertSame(['', '2026-08-28'], $resultContext['changeSet']['solvedAt']);
    }

    public function testMissingChangeSetInContextDoesNotErrorAndYieldsEmptyDocumentationFields(): void
    {
        $email = $this->builder->build(TechnicianOnCallMailSubject::TOC_UPDATED, $this->toc, []);
        $resultContext = $email->getContext();

        self::assertSame([], $resultContext['documentationFields']);
        self::assertSame([], $resultContext['changeSet']);
    }

    public function testAllThreeDocumentationFieldsSetTogetherAreAllExtracted(): void
    {
        $context = [
            'changeSet' => [
                'status' => ['IN_PROGRESS', 'SOLVED'],
                'symptoms' => ['', 'Symptoms text'],
                'originalSymptoms' => ['', 'Symptoms text'],
                'rootCause' => ['', 'Root cause text'],
                'originalRootCause' => ['', 'Root cause text'],
                'solution' => ['', 'Solution text'],
                'originalSolution' => ['', 'Solution text'],
            ],
        ];

        $email = $this->builder->build(TechnicianOnCallMailSubject::TOC_UPDATED, $this->toc, $context);
        $resultContext = $email->getContext();

        self::assertSame(['symptoms', 'rootCause', 'solution'], $resultContext['documentationFields']);
        self::assertSame(['status'], array_keys($resultContext['changeSet']));
    }
}
