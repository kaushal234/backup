<?php

declare(strict_types=1);

namespace App\Tests\LegacyBundle\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\ServiceActivity;
use App\Repository\EquipmentRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Doctrine\ORM\Extension\WarrantyClaimExtension;
use LegacyBundle\Entity\Quality\WarrantyClaim;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class WarrantyClaimExtensionTest extends TestCase
{
    public function testApplyToCollectionDoesNothingWhenResourceIsNotWarrantyClaim(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder
            ->expects(self::never())
            ->method('andWhere');

        $extension = new WarrantyClaimExtension(
            $this->createContainerWithUser(null),
        );

        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            \stdClass::class,
        );
    }

    public function testApplyToCollectionDoesNothingWhenUserIsNotExtranetUser(): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder
            ->expects(self::never())
            ->method('andWhere');

        $extension = new WarrantyClaimExtension(
            $this->createContainerWithUser(null),
        );

        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            WarrantyClaim::class,
        );
    }

    public function testApplyToCollectionFiltersWarrantyClaimsBySerialNumbers(): void
    {
        $user = $this->createMock(ExtranetUser::class);

        $serialNumbers = [
            'SN001',
            'SN002',
        ];

        $equipmentRecordRepository = $this->createMock(EquipmentRecordRepository::class);
        $equipmentRecordRepository
            ->expects(self::once())
            ->method('getEquipmentRecordSerialNumbersByExtranetUser')
            ->with($user)
            ->willReturn($serialNumbers);

        $andWhereArgs = [];
        $parameters = [];
        $queryBuilder = $this->createQueryBuilderExpectingSecurityFilter($andWhereArgs, $parameters);

        $extension = new WarrantyClaimExtension(
            $this->createContainerWithUser($user, $equipmentRecordRepository),
        );

        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            WarrantyClaim::class,
        );

        $this->assertSecurityFilterApplied($andWhereArgs, $parameters, $serialNumbers);
    }

    public function testApplyToItemFiltersWarrantyClaimBySerialNumbers(): void
    {
        $user = $this->createMock(ExtranetUser::class);

        $serialNumbers = [
            'SN001',
        ];

        $equipmentRecordRepository = $this->createMock(EquipmentRecordRepository::class);
        $equipmentRecordRepository
            ->expects(self::once())
            ->method('getEquipmentRecordSerialNumbersByExtranetUser')
            ->with($user)
            ->willReturn($serialNumbers);

        $andWhereArgs = [];
        $parameters = [];
        $queryBuilder = $this->createQueryBuilderExpectingSecurityFilter($andWhereArgs, $parameters);

        $extension = new WarrantyClaimExtension(
            $this->createContainerWithUser($user, $equipmentRecordRepository),
        );

        $extension->applyToItem(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            WarrantyClaim::class,
            ['id' => 58],
        );

        $this->assertSecurityFilterApplied($andWhereArgs, $parameters, $serialNumbers);
    }

    /**
     * @param list<string>         $andWhereArgs captures every andWhere() argument (cast to string)
     * @param array<string, mixed> $parameters   captures every setParameter() name/value pair
     */
    private function createQueryBuilderExpectingSecurityFilter(array &$andWhereArgs, array &$parameters): QueryBuilder
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $queryBuilder
            ->expects(self::once())
            ->method('getRootAliases')
            ->willReturn(['w']);

        $queryBuilder
            ->method('expr')
            ->willReturn(new Expr());

        // Sub-query builder shared by both exclusion filters (commissioning and confidential).
        $subQueryBuilder = $this->createMock(QueryBuilder::class);
        $subQueryBuilder->method('select')->willReturnSelf();
        $subQueryBuilder->method('from')->willReturnSelf();
        $subQueryBuilder->method('where')->willReturnSelf();
        $subQueryBuilder->method('andWhere')->willReturnSelf();
        $subQueryBuilder->method('getDQL')->willReturn('SELECT 1 FROM toc_subquery');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('createQueryBuilder')->willReturn($subQueryBuilder);

        $queryBuilder
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $queryBuilder
            ->expects(self::exactly(3))
            ->method('andWhere')
            ->willReturnCallback(static function (mixed $predicate) use ($queryBuilder, &$andWhereArgs): QueryBuilder {
                $andWhereArgs[] = (string) $predicate;

                return $queryBuilder;
            });

        $queryBuilder
            ->expects(self::exactly(3))
            ->method('setParameter')
            ->willReturnCallback(static function (string $key, mixed $value) use ($queryBuilder, &$parameters): QueryBuilder {
                $parameters[$key] = $value;

                return $queryBuilder;
            });

        return $queryBuilder;
    }

    /**
     * @param list<string>         $andWhereArgs
     * @param array<string, mixed> $parameters
     * @param list<string>         $serialNumbers
     */
    private function assertSecurityFilterApplied(array $andWhereArgs, array $parameters, array $serialNumbers): void
    {
        self::assertSame('w.serialNumber IN (:serialNumbersFromEquipmentRecords)', $andWhereArgs[0]);
        self::assertStringContainsString('NOT(EXISTS(', $andWhereArgs[1]);
        self::assertStringContainsString('NOT(EXISTS(', $andWhereArgs[2]);

        self::assertSame($serialNumbers, $parameters['serialNumbersFromEquipmentRecords']);
        self::assertSame(ServiceActivity::COMMISSIONING, $parameters['commissioningActivity']);
        self::assertSame('Y', $parameters['confidentialNotification']);
    }

    private function createContainerWithUser(
        ?object $user,
        ?EquipmentRecordRepository $equipmentRecordRepository = null,
    ): ContainerInterface {
        $security = $this->createMock(Security::class);
        $security
            ->method('getUser')
            ->willReturn($user);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnCallback(static function (string $service) use ($security, $equipmentRecordRepository) {
                return match ($service) {
                    Security::class => $security,
                    EquipmentRecordRepository::class => $equipmentRecordRepository,
                    default => throw new \LogicException(\sprintf('Unexpected service "%s".', $service)),
                };
            });

        return $container;
    }
}
