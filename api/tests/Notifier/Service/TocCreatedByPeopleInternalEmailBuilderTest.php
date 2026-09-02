<?php

declare(strict_types=1);

namespace App\Tests\Notifier\Service;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Product;
use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\Builders\Creation\TocCreatedByPeopleInternalEmailBuilder;
use App\Notifier\Service\TechnicianOnCall\RecipientsFinder;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;
use App\Repository\Sales\CustomerRelationshipTeamRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TocCreatedByPeopleInternalEmailBuilderTest extends KernelTestCase
{
    use ProphecyTrait;

    private TocCreatedByPeopleInternalEmailBuilder $builder;
    private TranslatorInterface|ObjectProphecy $translator;
    private ObjectProphecy $recipientsFinder;

    protected function setUp(): void
    {
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->recipientsFinder = $this->prophesize(RecipientsFinder::class);
        $security = $this->prophesize(Security::class);
        $crtRepository = $this->prophesize(CustomerRelationshipTeamRepository::class);
        $normalizer = $this->prophesize(NormalizerInterface::class);

        $this->builder = new TocCreatedByPeopleInternalEmailBuilder();
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
        self::assertTrue($this->builder->supports(TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL));
        self::assertFalse($this->builder->supports(TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_EXTERNAL));
    }

    public function testBuild(): void
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
        $tocProphecy->getId()->willReturn(753159);
        $tocProphecy->getOpenDays()->willReturn(1);

        $createdByProphecy = $this->prophesize(People::class);
        $createdByProphecy->getEmail()->willReturn('creator@example.com');

        $supervisorProphecy = $this->prophesize(People::class);
        $supervisorProphecy->getEmail()->willReturn('supervisor@example.com');

        $toc = $tocProphecy->reveal();
        $toc->createdBy = $createdByProphecy->reveal();
        $toc->equipmentRecord = $equipmentRecordProphecy->reveal();
        $toc->customer = $customerProphecy->reveal();

        $this->recipientsFinder
            ->findTos(TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL, $toc, [])
            ->willReturn(['creator@example.com', 'supervisor@example.com'])
            ->shouldBeCalledOnce();

        $this->recipientsFinder
            ->findCcs(TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL, $toc, [])
            ->willReturn([])
            ->shouldBeCalledOnce();

        $this->translator
            ->trans(
                \sprintf('toc.subject.%s', TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL->value),
                [
                    '%id%' => $toc->getId(),
                    '%productName%' => $toc->equipmentRecord->getProduct()->getName(),
                    '%openDays%' => $toc->getOpenDays(),
                    '%sso%' => $toc->equipmentRecord->getSalesOrganisation()->getName(),
                    '%customer%' => $toc->customer->getName(),
                ],
                'emails'
            )
            ->willReturn(
                'TOC#753159, Day 1, Sales Org, Customer De la Tranquillité, Taupe Model - OPEN and ASSIGNED'
            )
            ->shouldBeCalledOnce();

        $securityProphecy = $this->prophesize(Security::class);
        $securityProphecy->getUser()->willReturn($createdByProphecy->reveal());
        $crtRepository = $this->prophesize(CustomerRelationshipTeamRepository::class);
        $normalizer = $this->prophesize(NormalizerInterface::class);
        $this->builder = new TocCreatedByPeopleInternalEmailBuilder();
        $this->builder->setDependencies(
            $this->translator->reveal(),
            $this->recipientsFinder->reveal(),
            $securityProphecy->reveal(),
            $crtRepository->reveal(),
            $normalizer->reveal(),
        );

        $email = $this->builder->build(
            TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL,
            $toc
        );

        self::assertSame('creator@example.com', $email->getFrom()[0]->getAddress());

        $toAddresses = array_map(static fn ($addr) => $addr->getAddress(), $email->getTo());
        self::assertEqualsCanonicalizing(
            ['creator@example.com', 'supervisor@example.com'],
            $toAddresses
        );

        self::assertEmpty($email->getCc(), 'Expected no CCs');

        $expectedSubject = 'TOC#753159, Day 1, Sales Org, Customer De la Tranquillité, Taupe Model - OPEN and ASSIGNED';
        self::assertSame($expectedSubject, $email->getSubject());

        self::assertSame(
            \sprintf(
                'Emails/Service/TechnicianOnCall/%s.html.twig',
                TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL->value
            ),
            $email->getHtmlTemplate()
        );
    }
}
