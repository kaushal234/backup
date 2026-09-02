<?php

declare(strict_types=1);

namespace App\Filter\Support\EquipmentRecord;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Quality\Crab;
use App\Entity\Sales\Customer;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\Entity\Sales\Incoterm;
use App\Entity\Sales\OrderToFactory;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EquipmentRecordOdpFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_ODP = 'odpFilter';
    final public const NOT_SHIPPED = 'not_shipped';
    final public const GT_PAST = 'gt_past';
    final public const GT_NEXT = 'gt_next';
    final public const LATE = 'late';
    final public const SHIPPED_PAST = 'shipped_past';
    final public const SHIPPED_NEXT = 'shipped_next';
    final public const GT_NOT_SHIPPED = 'gt_not_shipped';
    final public const YT_NOT_SHIPPED = 'yt_not_shipped';
    final public const AVAILABLE_FOR_SALE = 'available_for_sale';
    final public const YT_CRAB = 'yt_crab';
    final public const GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_CUSTOMER_RESPONSIBLE = 'gt_not_shipped_no_estimated_pick_up_date_customer_responsible';
    final public const GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_TLD_RESPONSIBLE = 'gt_not_shipped_no_estimated_pick_up_date_tld_responsible';
    final public const GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_NOT_GRANTED = 'gt_not_shipped_no_estimated_pick_up_date_not_granted';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(self::FILTER_ODP)) {
            return;
        }

        $value = $request->query->get(static::FILTER_ODP);
        $rootAlias = $queryBuilder->getRootAliases()[0];

        switch ($value) {
            case self::NOT_SHIPPED:
                $queryBuilder->andWhere(\sprintf('%s.dateShipped IS NULL', $rootAlias));
                $queryBuilder->andWhere(\sprintf('%s.order IS NOT NULL', $rootAlias));
                break;
            case self::GT_PAST:
                $queryBuilder
                    ->andWhere(\sprintf('DATE_DIFF(NOW(), %s.greenTagDate) BETWEEN 0 AND 14', $rootAlias))
                    ->andWhere(\sprintf('%s.dateShipped IS NULL', $rootAlias));
                break;
            case self::GT_NEXT:
                $queryBuilder
                    ->andWhere(\sprintf('DATE_DIFF(%s.estimatedGreenTagDate, NOW()) BETWEEN 0 AND 14', $rootAlias))
                    ->andWhere(\sprintf('%s.dateShipped IS NULL', $rootAlias))
                    ->andWhere(\sprintf('%s.greenTagDate IS NULL', $rootAlias));
                break;
            case self::LATE:
                $queryBuilder
                    ->leftJoin(OrderToFactory::class, 'f', Join::WITH, \sprintf('%s.id = f.equipmentRecord', $rootAlias))
                    ->andWhere('DATE_DIFF(NOW(), f.factoryPromisedDeliveryDate) > 0')
                    ->andWhere(\sprintf('%s.dateShipped IS NULL', $rootAlias))
                    ->andWhere(\sprintf('%s.greenTagDate IS NULL', $rootAlias));
                break;
            case self::SHIPPED_PAST:
                $queryBuilder->andWhere(\sprintf('DATE_DIFF(NOW(), %s.dateShipped) BETWEEN 0 AND 14', $rootAlias));
                break;
            case self::SHIPPED_NEXT:
                $queryBuilder
//                    ->addSelect('MAX(esrl.id)')  #25969
                    ->leftJoin(EquipmentShippingRecordLine::class, 'esrl', Join::WITH, \sprintf('esrl.equipmentRecord = %s.id', $rootAlias))
                    ->andWhere('DATE_DIFF(esrl.estimatedPickUpDate, NOW()) between 0 AND 14');
                break;
            case self::GT_NOT_SHIPPED:
                $queryBuilder
                    ->andWhere(\sprintf('%s.greenTagDate IS NOT NULL', $rootAlias))
                    ->andWhere(\sprintf('DATE_DIFF(%s.greenTagDate, %s.yellowTagDate) > 0 OR DATE_DIFF(%s.greenTagDate, %s.yellowTagDate) IS NULL', $rootAlias, $rootAlias, $rootAlias, $rootAlias))
                    ->andWhere(\sprintf('%s.dateShipped IS NULL', $rootAlias));
                break;
            case self::YT_NOT_SHIPPED:
                $queryBuilder
                    ->andWhere(\sprintf('%s.yellowTagDate IS NOT NULL', $rootAlias))
                    ->andWhere(\sprintf('%s.dateShipped IS NULL', $rootAlias))
                    ->andWhere(\sprintf('%s.greenTagDate <= %s.yellowTagDate OR %s.greenTagDate IS NULL', $rootAlias, $rootAlias, $rootAlias));
                break;
            case self::AVAILABLE_FOR_SALE:
                $queryBuilder
                    ->leftJoin(Customer::class, 'cu', Join::WITH, \sprintf('%s.buyer = cu.id', $rootAlias))
                    ->andWhere(\sprintf('%s.dateShipped IS NULL', $rootAlias))
                    ->andWhere('cu.name IN (:customerName)')
                    ->setParameter('customerName', ['**AVAILABLE FOR SALE**', 'TLD EUROPE', 'TLD AMERICA', 'TLD ASIA']);
                break;
            case self::YT_CRAB:
                $queryBuilder
                    ->leftJoin(Crab::class, 'cr', Join::WITH, \sprintf('%s.id = cr.equipmentRecord', $rootAlias))
                    ->andWhere(\sprintf('%s.yellowTagDate IS NOT NULL', $rootAlias))
                    ->andWhere(\sprintf('(%s.greenTagDate < %s.yellowTagDate) OR %s.greenTagDate IS NULL', $rootAlias, $rootAlias, $rootAlias))
                    ->andWhere('cr.status != :closed')
                    ->setParameter('closed', Crab::CLOSED)
                    ->groupBy(\sprintf('%s.id', $rootAlias));
                break;
            case self::GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_CUSTOMER_RESPONSIBLE:
                $this->setUpNotShippedFilters($queryBuilder, $rootAlias);
                $queryBuilder
                    ->leftJoin(Incoterm::class, 'inc', Join::WITH, 'esr.incoterm = inc.id')
                    ->andWhere($queryBuilder->expr()->eq('esr.shipAuthorization', 'true'))
                    ->andWhere($queryBuilder->expr()->in('inc.code', [Incoterm::EXW, Incoterm::FCA]));
                break;
            case self::GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_TLD_RESPONSIBLE:
                $this->setUpNotShippedFilters($queryBuilder, $rootAlias);
                $queryBuilder
                    ->leftJoin(Incoterm::class, 'inc', Join::WITH, 'esr.incoterm = inc.id')
                    ->andWhere($queryBuilder->expr()->eq('esr.shipAuthorization', 'true'))
                    ->andWhere($queryBuilder->expr()->notIn('inc.code', [Incoterm::EXW, Incoterm::FCA]));
                break;
            case self::GT_NOT_SHIPPED_NO_ESTIMATED_PICK_UP_DATE_NOT_GRANTED:
                $this->setUpNotShippedFilters($queryBuilder, $rootAlias);
                $queryBuilder->andWhere($queryBuilder->expr()->eq('esr.shipAuthorization', 'false'));
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_ODP => [
                'property' => static::FILTER_ODP,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

    private function setUpNotShippedFilters(QueryBuilder $queryBuilder, string $rootAlias): QueryBuilder
    {
        return
            $queryBuilder
                ->leftJoin(EquipmentShippingRecordLine::class, 'esrl', Join::WITH, \sprintf('esrl.equipmentRecord = %s.id', $rootAlias))
                ->leftJoin(EquipmentShippingRecord::class, 'esr', Join::WITH, 'esrl.equipmentShippingRecord = esr.id')
                ->andWhere(\sprintf('%s.greenTagDate IS NOT NULL', $rootAlias))
                ->andWhere(\sprintf('(DATE_DIFF(%s.greenTagDate, %s.yellowTagDate) > 0 OR DATE_DIFF(%s.greenTagDate, %s.yellowTagDate) IS NULL)', $rootAlias, $rootAlias, $rootAlias, $rootAlias))
                ->andWhere(\sprintf('%s.dateShipped IS NULL', $rootAlias))
                ->andWhere('esrl.estimatedPickUpDate IS NULL')
                ->andWhere('esrl.id IS NOT NULL')
        ;
    }
}
