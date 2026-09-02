<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\EventListener\Service;

use App\Doctrine\EventListener\Service\TechnicianOnCallDeletionListener;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use LegacyBundle\Entity\WarrantyClaim;
use LegacyBundle\Manager\ModLinkManager;
use LegacyBundle\Repository\WarrantyClaimRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TechnicianOnCallDeletionListenerTest extends TestCase
{
    /** @var ModLinkManager&MockObject */
    private ModLinkManager $modLinkManager;

    /** @var Connection&MockObject */
    private Connection $legacyConnection;

    /** @var WarrantyClaimRepository&MockObject */
    private WarrantyClaimRepository $warrantyClaimRepository;

    private TechnicianOnCallDeletionListener $listener;

    protected function setUp(): void
    {
        $this->modLinkManager = $this->createMock(ModLinkManager::class);
        $this->legacyConnection = $this->createMock(Connection::class);
        $this->warrantyClaimRepository = $this->createMock(WarrantyClaimRepository::class);

        $this->listener = new TechnicianOnCallDeletionListener(
            $this->modLinkManager,
            $this->legacyConnection,
            $this->warrantyClaimRepository
        );
    }

    public function testPostRemoveWithNonTechnicianOnCall(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $args = new PostRemoveEventArgs(new \stdClass(), $entityManager);

        $this->modLinkManager->expects(self::never())->method('getLinks');

        $this->listener->postRemove($args);
    }

    public function testPostRemoveWithoutLegacyId(): void
    {
        /** @var TechnicianOnCall&MockObject $toc */
        $toc = $this->createMock(TechnicianOnCall::class);
        $toc->method('getLegacyId')->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $args = new PostRemoveEventArgs($toc, $entityManager);

        $this->modLinkManager->expects(self::never())->method('getLinks');

        $this->listener->postRemove($args);
    }

    public function testPostRemoveDeletesOnlyPendingWarrantyClaims(): void
    {
        /** @var TechnicianOnCall&MockObject $toc */
        $toc = $this->createMock(TechnicianOnCall::class);
        $toc->method('getLegacyId')->willReturn(123);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $args = new PostRemoveEventArgs($toc, $entityManager);

        $links = [
            ['id' => 1, 'item' => 10, 'type' => 'WC'], // Pending
            ['id' => 2, 'item' => 11, 'type' => 'WC'], // Non-pending
            ['id' => 3, 'item' => 12, 'type' => 'WC'], // Not found
        ];

        $this->modLinkManager->expects(self::once())
            ->method('getLinks')
            ->with(TechnicianOnCall::MODULE_NAME, 123, 'WC')
            ->willReturn($links);

        $wc10 = new WarrantyClaim();
        $wc10->status = WarrantyClaim::PENDING;

        $wc11 = new WarrantyClaim();
        $wc11->status = 'VALIDATED';

        $this->warrantyClaimRepository->expects(self::exactly(3))
            ->method('find')
            ->willReturnMap([
                [10, null, null, $wc10],
                [11, null, null, $wc11],
                [12, null, null, null],
            ]);

        // Should only delete link 1 (item 10)
        $this->legacyConnection->expects(self::exactly(2))
            ->method('delete')
            ->willReturnCallback(static function (string $table, array $identifier) {
                static $callCount = 0;
                ++$callCount;

                if (1 === $callCount) {
                    TestCase::assertSame('warranty', $table);
                    TestCase::assertSame(['id' => 10], $identifier);
                } elseif (2 === $callCount) {
                    TestCase::assertSame('mod_links', $table);
                    TestCase::assertSame(['id' => 1], $identifier);
                }

                return 1;
            });

        $this->listener->postRemove($args);
    }

    public function testPostRemoveDeletesNothingIfNoLinks(): void
    {
        /** @var TechnicianOnCall&MockObject $toc */
        $toc = $this->createMock(TechnicianOnCall::class);
        $toc->method('getLegacyId')->willReturn(123);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $args = new PostRemoveEventArgs($toc, $entityManager);

        $this->modLinkManager->expects(self::once())
            ->method('getLinks')
            ->willReturn([]);

        $this->warrantyClaimRepository->expects(self::never())->method('find');
        $this->legacyConnection->expects(self::never())->method('delete');

        $this->listener->postRemove($args);
    }
}
