<?php

declare(strict_types=1);

namespace Tests\AppBundle\Twig\Extension;

use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Twig\Extension\TroubleTicketMenuContextExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class TroubleTicketMenuContextExtensionTest extends TestCase
{
    private const USER_IRI = '/api/users/42';
    private const OTHER_IRI = '/api/users/99';
    private const MOO_IRI = '/api/users/10';
    private const KEY_USER_IRI = '/api/users/20';
    private const LOCAL_KEY_USER_IRI = '/api/users/30';
    private const TICKET_IRI = '/api/trouble_tickets/1';

    public function testGetFunctionsExposesTroubleTicketMenuContext(): void
    {
        $extension = new TroubleTicketMenuContextExtension($this->createMock(Security::class));

        $functions = $extension->getFunctions();

        self::assertCount(1, $functions);
        self::assertSame('trouble_ticket_menu_context', $functions[0]->getName());
    }

    public function testGetContextWithNoUserReturnsAllFlagsFalseOrDefault(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);
        $security->method('isGranted')->willReturn(false);

        $extension = new TroubleTicketMenuContextExtension($security);
        $context = $extension->getContext(new ApiData([]));

        self::assertSame([
            'isLocalKeyUser' => false,
            'assigneeIsNotLocalKeyUser' => true,
            'troubleTicketIsOpen' => true,
            'troubleTicketIsOnMisSide' => false,
            'troubleTicketIsOnUserSide' => false,
            'isMoo' => false,
            'isKeyUser' => false,
            'isAssignee' => false,
            'isAssignor' => false,
            'canTransfer' => false,
            'assigneeIsNotTheMOO' => true,
            'assigneeIsNotKeyUser' => true,
            'isMIS' => false,
            'isIncident' => false,
            'isRequest' => false,
            'isNotMISAssignee' => false,
        ], $context);
    }

    public function testGetContextDetectsCurrentUserAsMooAssigneeAssignorAndLocalKeyUser(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->createUser(self::USER_IRI));
        $security->method('isGranted')->willReturnCallback(
            static fn (string $attribute, mixed $subject = null): bool => match (true) {
                'FEATURE_TROUBLE_TICKET_TRANSFER_VOTER' === $attribute && self::TICKET_IRI === $subject => true,
                'ACL_GG_MIS' === $attribute => true,
                default => false,
            }
        );

        $ticket = new ApiData([
            '@id' => self::TICKET_IRI,
            'status' => 'IN PROGRESS',
            'assignee' => ['@id' => self::USER_IRI],
            'createdBy' => ['@id' => self::USER_IRI],
            'misAssignee' => ['@id' => self::USER_IRI],
            'type' => ['type' => 'Incident'],
            'module' => [
                'operationalOwner' => ['@id' => self::USER_IRI],
                'keyUser' => ['@id' => self::USER_IRI],
                'localKeyUsers' => [
                    ['@id' => self::USER_IRI],
                    ['@id' => self::OTHER_IRI],
                ],
            ],
        ]);

        $context = (new TroubleTicketMenuContextExtension($security))->getContext($ticket);

        self::assertTrue($context['isLocalKeyUser']);
        self::assertFalse($context['assigneeIsNotLocalKeyUser']);
        self::assertTrue($context['troubleTicketIsOpen']);
        self::assertTrue($context['troubleTicketIsOnMisSide']);
        self::assertFalse($context['troubleTicketIsOnUserSide']);
        self::assertTrue($context['isMoo']);
        self::assertTrue($context['isKeyUser']);
        self::assertTrue($context['isAssignee']);
        self::assertTrue($context['isAssignor']);
        self::assertTrue($context['canTransfer']);
        self::assertFalse($context['assigneeIsNotTheMOO']);
        self::assertFalse($context['assigneeIsNotKeyUser']);
        self::assertTrue($context['isMIS']);
        self::assertTrue($context['isIncident']);
        self::assertFalse($context['isRequest']);
        self::assertFalse($context['isNotMISAssignee']);
    }

    public function testGetContextWhenCurrentUserIsNotInvolved(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->createUser(self::USER_IRI));
        $security->method('isGranted')->willReturn(false);

        $ticket = new ApiData([
            '@id' => self::TICKET_IRI,
            'status' => 'SOLUTION PROPOSED',
            'assignee' => ['@id' => self::OTHER_IRI],
            'createdBy' => ['@id' => self::OTHER_IRI],
            'misAssignee' => ['@id' => self::OTHER_IRI],
            'type' => ['type' => 'Request'],
            'module' => [
                'operationalOwner' => ['@id' => self::MOO_IRI],
                'keyUser' => ['@id' => self::KEY_USER_IRI],
                'localKeyUsers' => [['@id' => self::LOCAL_KEY_USER_IRI]],
            ],
        ]);

        $context = (new TroubleTicketMenuContextExtension($security))->getContext($ticket);

        self::assertFalse($context['isLocalKeyUser']);
        self::assertTrue($context['assigneeIsNotLocalKeyUser']);
        self::assertTrue($context['troubleTicketIsOpen']);
        self::assertFalse($context['troubleTicketIsOnMisSide']);
        self::assertTrue($context['troubleTicketIsOnUserSide']);
        self::assertFalse($context['isMoo']);
        self::assertFalse($context['isKeyUser']);
        self::assertFalse($context['isAssignee']);
        self::assertFalse($context['isAssignor']);
        self::assertFalse($context['canTransfer']);
        self::assertTrue($context['assigneeIsNotTheMOO']);
        self::assertTrue($context['assigneeIsNotKeyUser']);
        self::assertFalse($context['isMIS']);
        self::assertFalse($context['isIncident']);
        self::assertTrue($context['isRequest']);
        self::assertTrue($context['isNotMISAssignee']);
    }

    /**
     * @dataProvider closedStatusProvider
     */
    public function testTroubleTicketIsOpenIsFalseForClosedStatuses(string $status): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);
        $security->method('isGranted')->willReturn(false);

        $ticket = new ApiData(['status' => $status]);
        $context = (new TroubleTicketMenuContextExtension($security))->getContext($ticket);

        self::assertFalse($context['troubleTicketIsOpen']);
        self::assertFalse($context['troubleTicketIsOnMisSide']);
        self::assertFalse($context['troubleTicketIsOnUserSide']);
    }

    /**
     * @return iterable<array{string}>
     */
    public static function closedStatusProvider(): iterable
    {
        yield ['SOLVED'];
        yield ['NOT AN ISSUE'];
        yield ['ALREADY RAISED'];
        yield ['NOT APPROVED'];
    }

    public function testCanTransferIsFalseWhenTicketIriIsMissing(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->createUser(self::USER_IRI));
        $security->expects(self::once())
            ->method('isGranted')
            ->with('ACL_GG_MIS')
            ->willReturn(false);

        $context = (new TroubleTicketMenuContextExtension($security))->getContext(new ApiData([]));

        self::assertFalse($context['canTransfer']);
    }

    private function createUser(string $iriId): User
    {
        return new User(
            iriId: $iriId,
            iriType: 'User',
            username: 'jdoe',
            firstname: 'John',
            lastname: 'Doe',
            disabled: false,
            hidden: false,
            expirationDate: '2099-01-01',
            photo: [],
            roles: [],
            acls: [],
            businessUnit: null,
            token: 'token',
        );
    }
}
