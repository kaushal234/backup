<?php

declare(strict_types=1);

namespace App\Repository\Directory;

use App\Command\Directory\HideAndUnhideDisabledPeopleCommand;
use App\Entity\Acl;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\ContractType;
use App\Entity\Directory\Department;
use App\Entity\Directory\Division;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\Phone;
use App\Entity\Directory\Position;
use App\Entity\Directory\PositionLevel;
use App\Entity\Directory\Premise;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use App\Entity\Group;
use App\Entity\Module\Module;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Entity\Sales\MarketIntelligence\MarketIntelligenceSubscription;
use App\Repository\Module\ModuleRepository;
use App\Repository\Sales\CustomerRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PeopleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, People::class);
    }

    public function replacePeopleDepartment(Department $source, Department $target)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->update()
            ->set('p.department', ':target')
            ->where('p.department = :source')
            ->setParameters(new ArrayCollection([
                new Parameter('target', $target),
                new Parameter('source', $source),
            ]))
        ;

        $qb->getQuery()->execute();
    }

    public function disabledPeople(People $people)
    {
        $people->setDisabled(true);
        $people->setEnableAt(null);
        $people->setAlternateEmail(null);

        if (null !== $photo = $people->getPhoto()) {
            $people->removeFile($photo);
        }

        foreach ($people->getPhones()->toArray() as $phone) {
            if (Phone::TYPE_MOBILE_ALTERNATE === $phone->getType()) {
                $people->removePhone($phone);
            }
        }

        $this->getEntityManager()->persist($people);
        $this->getEntityManager()->flush();
    }

    public function enableAndUnhidePeople(People $people)
    {
        $people->setDisabled(false);
        $people->setHidden(false);

        $this->getEntityManager()->persist($people);
        $this->getEntityManager()->flush();
    }

    public function unhidePeople(People $people): void
    {
        $people->setHidden(false);

        $this->getEntityManager()->persist($people);
        $this->getEntityManager()->flush();
    }

    public function hidePeople(People $people): void
    {
        $people->setHidden(true);

        $this->getEntityManager()->persist($people);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array|People[]
     */
    public function findGroupMembers(string $group, ?Location $location = null): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->addSelect(['acls', 'g'])
            ->join('p.acls', 'acls')
            ->leftJoin('acls.group', 'g')
            ->where('g.name = :group')
            ->andWhere('p.hidden = :hidden')
            ->andWhere('p.disabled = :disabled')
            ->setParameters(new ArrayCollection([
                new Parameter('group', $group),
                new Parameter('hidden', false),
                new Parameter('disabled', false),
            ]))
        ;

        if (null !== $location) {
            $qb
                ->andWhere('acls.location = :location')
                ->setParameter('location', $location)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findGroupMembersId(string $group): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.id')
            ->distinct()
            ->join('p.acls', 'acls')
            ->leftJoin('acls.group', 'g')
            ->where('g.name = :group')
        ->setParameters(new ArrayCollection([
            new Parameter('group', $group),
        ]))
        ;
        $arrayResult = $qb->getQuery()->getArrayResult();

        return array_reduce($arrayResult, static function ($membersId, $people) {
            $membersId[] = $people['id'];

            return $membersId;
        }, []);
    }

    /**
     * @return array|People[]
     */
    public function findGroupsMembers(array $groups, ?Location $location = null): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->addSelect(['acls', 'g'])
            ->join('p.acls', 'acls')
            ->leftJoin('acls.group', 'g')
            ->where($qb->expr()->in('g.name', ':groups'))
            ->andWhere('p.hidden = :hidden')
            ->andWhere('p.disabled = :disabled')
            ->setParameters(new ArrayCollection([
                new Parameter('groups', $groups),
                new Parameter('hidden', false),
                new Parameter('disabled', false),
            ]))
        ;

        if (null !== $location) {
            $qb
                ->andWhere('acls.location = :location')
                ->setParameter('location', $location)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array|People[]
     */
    public function findGroupsMembersByRegion(array $groups, Region|string $region): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->addSelect(['acls', 'g'])
            ->join('p.acls', 'acls')
            ->leftJoin('acls.group', 'g')
            ->leftJoin('acls.location', 'lo')
            ->leftJoin('lo.businessUnit', 'bu')
            ->where($qb->expr()->in('g.name', ':groups'))
            ->andWhere('p.hidden = :hidden')
            ->andWhere('p.disabled = :disabled')
            ->setParameters(new ArrayCollection([
                new Parameter('groups', $groups),
                new Parameter('hidden', false),
                new Parameter('disabled', false),
                new Parameter('region', $region),
            ]))
        ;

        if ($region instanceof Region) {
            $qb->andWhere('bu.region = :region');
        } else {
            $qb
                ->leftJoin('bu.region', 'region')
                ->andWhere('region.name = :region')
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array|People[]
     */
    public function findPeopleToLinkWithExtranetAccount(): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->join(ExtranetUser::class, 'xu', Join::WITH, 'p.email = xu.email')
            ->where('p.email = xu.email')
            ->andWhere('p.hidden = :hidden')
            ->andWhere('p.disabled = :disabled')
            ->andWhere($qb->expr()->isNull('p.extranetUserLinked'))
            ->setParameters(new ArrayCollection([
                new Parameter('hidden', false),
                new Parameter('disabled', false),
            ]))
        ;

        return $qb->getQuery()->getResult();
    }

    public function getSubordinates(People $people, int $depth): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb->andWhere('p.hidden = :hidden')
            ->andWhere('p.disabled = :disabled')
            ->setParameters(new ArrayCollection([
                new Parameter('hidden', false),
                new Parameter('disabled', false),
            ]));

        $orStatements = $qb->expr()->orX();

        $qb->leftJoin(People::class, 'subordinate_level_1', Join::ON, 'subordinate_level_1.id = p.supervisor');
        $orStatements->add($qb->expr()->eq('subordinate_level_1.id', $people->getId()));

        for ($i = 2; $i <= $depth; ++$i) {
            $condition = \sprintf('subordinate_level_%s.id', $i).\sprintf('= subordinate_level_%s.supervisor', $i - 1);
            $qb->leftJoin(People::class, \sprintf('subordinate_level_%s', $i), Join::WITH, $condition);
            $orStatements->add($qb->expr()->eq(\sprintf('subordinate_level_%s.id', $i), $people->getId()));
        }

        $qb->andWhere($orStatements);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array|People[]
     */
    public function findNewPeopleWithNoSubscriptions(MarketIntelligence $marketIntelligence): array
    {
        /** @var ModuleRepository $moduleRepository */
        $moduleRepository = $this->getEntityManager()->getRepository(Module::class);
        $mimModule = $moduleRepository->findByName('MIM');

        if (!$mimModule instanceof Module || null === $mimModule->getMigratedAt()) {
            return [];
        }

        $qb = $this->createQueryBuilder('p');

        $subQueryBuilder = $this->getEntityManager()->getRepository(MarketIntelligenceSubscription::class)->createQueryBuilder('mim_sub');

        $subQueryBuilder
            ->select($subQueryBuilder->expr()->count('mim_sub.id'))
            ->where('p.id = mim_sub.subscriber');

        $qb
            ->leftjoin(Acl::class, 'acls', Join::WITH, 'p.id = acls.user')
            ->leftjoin(Group::class, 'groups', Join::WITH, 'acls.group = groups.id')
            ->where('p.createdAt > :migrationDate')
            ->andWhere('groups.name = :group')
            ->andWhere('p.hidden = :hidden')
            ->andWhere('p.disabled = :disabled')
            ->andWhere(\sprintf('(%s) < 1', $subQueryBuilder->getQuery()->getDQL()))
            ->setParameter('hidden', false)
            ->setParameter('disabled', false)
            ->setParameter('migrationDate', $mimModule->getMigratedAt())
            ->setParameter('group', 'ACL_AUTH_INTRANET')
        ;

        if (!$marketIntelligence->getDivisions()->isEmpty()) {
            $qb
                ->leftJoin(BusinessUnit::class, 'bu', Join::WITH, 'p.businessUnit = bu')
                ->leftJoin(Region::class, 'region', Join::WITH, 'bu.region = region')
                ->leftJoin(SubDivision::class, 'sub_division', Join::WITH, 'region.subDivision = sub_division')
                ->leftJoin(Division::class, 'division', Join::WITH, 'sub_division.division = division')
                ->andWhere($qb->expr()->in('division', ':divisions'))
                ->setParameter('divisions', $marketIntelligence->getDivisions())
            ;
        }

        if (!$marketIntelligence->getPositionLevels()->isEmpty()) {
            $qb
                ->leftJoin(Position::class, 'position', Join::WITH, 'p.position = position')
                ->leftJoin(PositionLevel::class, 'position_level', Join::WITH, 'position.level = position_level')
                ->andWhere($qb->expr()->in('position_level', ':positionLevels'))
                ->setParameter('positionLevels', $marketIntelligence->getPositionLevels())
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function getIdentifiersForBusinessUnit(BusinessUnit $businessUnit): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select('p.id')
            ->where('p.businessUnit = :businessUnit')
            ->setParameter('businessUnit', $businessUnit)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function getIdentifiersForDepartment(Department $department): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select('p.id')
            ->where('p.department = :department')
            ->setParameter('department', $department)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function getIdentifiersForPosition(Position $position): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select('p.id')
            ->where('p.position = :position')
            ->setParameter('position', $position)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function countPremiseUsers(Premise $premise): int
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('COUNT(DISTINCT p.id) AS count')
            ->andWhere('p.premise = :premise')
            ->andWhere('p.disabled = :disabled')
            ->setParameters(new ArrayCollection([
                new Parameter('premise', $premise->getId()),
                new Parameter('disabled', false),
            ]))
        ;

        return $qb->getQuery()->getSingleResult()['count'];
    }

    public function countUncategorizedPeopleByBusinessUnits(BusinessUnit ...$businessUnits): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('COUNT(DISTINCT p.id) AS count, bu.name')
            ->join('p.businessUnit', 'bu')
            ->andWhere('p.disabled = :disabled')
            ->groupBy('bu.name')
            ->setParameter('disabled', false)
        ;

        $orStatements = $qb->expr()->orX();

        foreach ($businessUnits as $businessUnit) {
            $categorizedPositions = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('pos.id')
                ->from(Position::class, 'pos')
                ->innerJoin('pos.positionClassifications', 'pc')
                ->andWhere('pc.businessUnit = :businessUnit')
                ->setParameter('businessUnit', $businessUnit)
                ->getQuery()
                ->getScalarResult()
            ;

            $businessUnitParameter = \sprintf('businessUnit%s', $businessUnit->getId());
            $categorizedPositionsParameter = \sprintf('categorizedPositions%s', $businessUnit->getId());

            $businessUnitStatement = $qb->expr()->andX();
            $businessUnitStatement->add(\sprintf('p.businessUnit = :%s', $businessUnitParameter));
            $qb->setParameter($businessUnitParameter, $businessUnit);

            $positionOrStatement = $qb->expr()->orX();
            $positionOrStatement->add('p.position IS NULL');

            if ([] === $categorizedPositions) {
                $positionOrStatement->add('p.position IS NOT NULL');
            } else {
                $positionOrStatement->add(\sprintf('p.position NOT IN (:%s)', $categorizedPositionsParameter));
                $qb->setParameter($categorizedPositionsParameter, $categorizedPositions);
            }

            $businessUnitStatement->add($positionOrStatement);
            $orStatements->add($businessUnitStatement);
        }

        $qb->andWhere($orStatements);

        return $qb->getQuery()->getScalarResult();
    }

    public function findUncategorizedPeopleByBusinessUnits(BusinessUnit ...$businessUnits): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->join('p.businessUnit', 'bu')
            ->andWhere('p.disabled = :disabled')
            ->groupBy('bu.name')
            ->setParameter('disabled', false)
        ;

        $orStatements = $qb->expr()->orX();

        foreach ($businessUnits as $businessUnit) {
            $categorizedPositions = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('pos.id')
                ->from(Position::class, 'pos')
                ->innerJoin('pos.positionClassifications', 'pc')
                ->andWhere('pc.businessUnit = :businessUnit')
                ->setParameter('businessUnit', $businessUnit)
                ->getQuery()
                ->getScalarResult()
            ;

            $businessUnitParameter = \sprintf('businessUnit%s', $businessUnit->getId());
            $categorizedPositionsParameter = \sprintf('categorizedPositions%s', $businessUnit->getId());

            $businessUnitStatement = $qb->expr()->andX();
            $businessUnitStatement->add(\sprintf('p.businessUnit = :%s', $businessUnitParameter));
            $qb->setParameter($businessUnitParameter, $businessUnit);

            $positionOrStatement = $qb->expr()->orX();
            $positionOrStatement->add('p.position IS NULL');

            if ([] === $categorizedPositions) {
                $positionOrStatement->add('p.position IS NOT NULL');
            } else {
                $positionOrStatement->add(\sprintf('p.position NOT IN (:%s)', $categorizedPositionsParameter));
                $qb->setParameter($categorizedPositionsParameter, $categorizedPositions);
            }

            $businessUnitStatement->add($positionOrStatement);
            $orStatements->add($businessUnitStatement);
        }

        $qb->andWhere($orStatements);

        return $qb->getQuery()->getScalarResult();
    }

    public function findOneByPositionByLocation(Position $position, Location $location): ?People
    {
        $queryBuilder = $this->createByPositionByLocationQueryBuilder($position, $location);
        $queryBuilder->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /** @return People[] */
    public function findAllByPositionByLocation(Position $position, Location $location): array
    {
        $queryBuilder = $this->createByPositionByLocationQueryBuilder($position, $location);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByPositionByDivision(Position $position, Division $division)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->leftJoin('p.businessUnit', 'bu')
            ->leftJoin('bu.region', 'r')
            ->leftJoin('r.subDivision', 's')
            ->leftJoin('s.division', 'd')
            ->where('d = :division')
            ->andWhere('p.position = :position')
            ->andWhere('p.disabled = :false')
            ->andWhere('p.hidden = :false')
            ->setParameter(':division', $division)
            ->setParameter(':position', $position)
            ->setParameter(':false', false)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findPeopleToActivate(?\DateTimeInterface $since = null)
    {
        $since ??= new \DateTime();

        $qb = $this->createQueryBuilder('p');
        $qb
            ->where('p.enableAt >= :startOfTheDay')
            ->andWhere('p.enableAt <= :endOfTheNextDay')
            ->setParameter(':startOfTheDay', $since->format('Y-m-d 00:00:00'))
            ->setParameter(':endOfTheNextDay', (new \DateTime('+ 1 days'))->format('Y-m-d 23:59:59'))
        ;

        return $qb->getQuery()->getResult();
    }

    public function findDisabledPeopleToUnhide()
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->where('p.hidden = 1')
            ->andWhere('p.disabled = 1')
            ->andWhere($qb->expr()->isNotNull('p.enableAt'))
            ->andWhere('p.enableAt <= :LIMIT_VISIBILITY_BEFORE_ARRIVAL')
            ->andWhere('p.enableAt >= :today')
            ->setParameter('today', (new \DateTime())->format('Y-m-d 00:00:00'))
            ->setParameter('LIMIT_VISIBILITY_BEFORE_ARRIVAL', (new \DateTime(HideAndUnhideDisabledPeopleCommand::LIMIT_VISIBILITY_BEFORE_ARRIVAL))->format('Y-m-d 23:59:59'))
        ;

        return $qb->getQuery()->getResult();
    }

    public function findDisabledPeopleToHide()
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->where('p.hidden = 0')
            ->andWhere('p.disabled = 1')
            ->andWhere($qb->expr()->isNotNull('p.disabledAt'))
            ->andWhere('p.disabledAt <= :LIMIT_VISIBILITY_AFTER_DEPARTURE')
            ->setParameter('LIMIT_VISIBILITY_AFTER_DEPARTURE', (new \DateTime(HideAndUnhideDisabledPeopleCommand::LIMIT_VISIBILITY_AFTER_DEPARTURE))->setTime(0, 0, 0))
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return People[]
     */
    public function searchPeopleForJaveloSynchronization(array $contractTypes, array $excludeDivisions, array $excludeBusinessUnits, array $excludePeople, string $disabledAtThreshold, ?int $peopleId = null): array
    {
        // First QueryBuilder for basic people
        $qb1 = $this->createQueryBuilder('p');
        $qb1
            ->select('p.id')
            ->leftJoin('p.contractType', 'ct')
            ->leftJoin('p.businessUnit', 'bu')
            ->leftJoin('bu.region', 'r')
            ->leftJoin('r.subDivision', 'sd')
            ->leftJoin('sd.division', 'd')
            ->where($qb1->expr()->in('ct.id', ':contractTypes'))
            ->andWhere($qb1->expr()->notIn('d.id', ':excludeDivisions'))
            ->andWhere($qb1->expr()->notIn('bu.id', ':excludeBusinessUnits'))
            ->andWhere($qb1->expr()->notIn('p.id', ':excludePeople'))
            ->andWhere('p.username IS NOT NULL')
            ->setParameter('contractTypes', $contractTypes)
            ->setParameter('excludeDivisions', $excludeDivisions)
            ->setParameter('excludeBusinessUnits', $excludeBusinessUnits)
            ->setParameter('excludePeople', $excludePeople)
            ->andWhere(
                $qb1->expr()->orX(
                    $qb1->expr()->eq('p.disabled', 0),
                    $qb1->expr()->andX(
                        $qb1->expr()->eq('p.disabled', 1),
                        $qb1->expr()->gt('p.disabledAt', ':disabledAtThreshold')
                    ),
                    $qb1->expr()->andX(
                        $qb1->expr()->eq('p.disabled', 1),
                        $qb1->expr()->isNull('p.disabledAt'),
                        $qb1->expr()->gt('p.createdAt', ':disabledAtThreshold')
                    )
                )
            )
            ->setParameter('disabledAtThreshold', $disabledAtThreshold);

        // Second QueryBuilder for consulting supervisor
        $qb2 = $this->createQueryBuilder('sup');
        $qb2
            ->select('sup.id')
            ->where('sup.contractType = 3')
            ->andWhere(
                $qb2->expr()->exists(
                    $this->createQueryBuilder('sub')
                        ->select('1')
                        ->leftJoin('sub.contractType', 'ct')
                        ->leftJoin('sub.businessUnit', 'bu')
                        ->leftJoin('bu.region', 'r')
                        ->leftJoin('r.subDivision', 'sd')
                        ->leftJoin('sd.division', 'd')
                        ->where($qb2->expr()->in('ct.id', ':contractTypes'))
                        ->andWhere($qb2->expr()->notIn('d.id', ':excludeDivisions'))
                        ->andWhere($qb2->expr()->notIn('bu.id', ':excludeBusinessUnits'))
                        ->andWhere($qb2->expr()->notIn('sub.id', ':excludePeople'))
                        ->andWhere('sub.username IS NOT NULL')
                        ->andWhere(
                            $qb2->expr()->orX(
                                $qb2->expr()->eq('sub.disabled', 0),
                                $qb2->expr()->andX(
                                    $qb2->expr()->eq('sub.disabled', 1),
                                    $qb2->expr()->gt('sub.disabledAt', ':disabledAtThreshold')
                                ),
                                $qb2->expr()->andX(
                                    $qb2->expr()->eq('sub.disabled', 1),
                                    $qb2->expr()->isNull('sub.disabledAt'),
                                    $qb2->expr()->gt('sub.createdAt', ':disabledAtThreshold')
                                )
                            )
                        )
                        ->andWhere('sub.supervisor = sup.id')
                        ->getDQL()
                )
            )
            ->setParameter('contractTypes', $contractTypes)
            ->setParameter('excludeDivisions', $excludeDivisions)
            ->setParameter('excludeBusinessUnits', $excludeBusinessUnits)
            ->setParameter('excludePeople', $excludePeople)
            ->setParameter('disabledAtThreshold', $disabledAtThreshold);

        if (null !== $peopleId) {
            $qb1->andWhere('p.id = :peopleId')
                ->setParameter('peopleId', $peopleId);
            $qb2->andWhere('sup.id = :peopleId')
                ->setParameter('peopleId', $peopleId);
        }

        // Combine results
        $query1 = $qb1->getQuery()->getResult();
        $query2 = $qb2->getQuery()->getResult();

        return array_merge($query1, $query2);
    }

    /**
     * @return People[]
     */
    public function searchPeopleForAgileSynchronization(array $excludeDivisions, array $excludeBusinessUnits, array $excludePositions, array $excludePeople, ?int $peopleId = null): array
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $andX = $queryBuilder->expr()->andX();
        $andX
            ->add($queryBuilder->expr()->in('pos.id', ':excludePositions'))
            ->add($queryBuilder->expr()->neq('contractType.name', ':temps'))
        ;

        $orX = $queryBuilder->expr()->orX();
        $orX
            ->add($queryBuilder->expr()->notIn('pos.id', ':excludePositions'))
            ->add($andX)
        ;

        $queryBuilder->leftJoin('p.acls', 'a')
            ->leftJoin('a.group', 'g')
            ->leftJoin('p.businessUnit', 'bu')
            ->leftJoin('bu.region', 'r')
            ->leftJoin('r.subDivision', 'sd')
            ->leftJoin('sd.division', 'd')
            ->leftJoin('p.position', 'pos')
            ->leftJoin('p.contractType', 'contractType')
            ->where($queryBuilder->expr()->notIn('d.id', ':excludeDivisions'))
            ->andWhere($queryBuilder->expr()->notIn('p.businessUnit', ':excludeBusinessUnits'))
            ->andWhere($queryBuilder->expr()->notIn('p.id', ':excludePeople'))
            ->andWhere($orX)
            ->groupBy('p.id')
            ->setParameter('excludeDivisions', array_values($excludeDivisions))
            ->setParameter('excludeBusinessUnits', array_values($excludeBusinessUnits))
            ->setParameter('excludePositions', array_values($excludePositions))
            ->setParameter('excludePeople', array_values($excludePeople))
            ->setParameter('temps', ContractType::TEMP_AND_CONSULTANTS)
        ;

        if (null !== $peopleId) {
            $queryBuilder
                ->andWhere('p.id = :peopleId')
                ->setParameter('peopleId', $peopleId);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function searchAllPeopleIdWithGroup(string $group, ?int $peopleId = null): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->select('p.id')
            ->join('p.acls', 'acls')
            ->leftJoin('acls.group', 'g')
            ->where($qb->expr()->in('g.name', ':groups'))
            ->setParameters(new ArrayCollection([
                new Parameter('groups', [$group]),
            ]))
        ;

        if (null !== $peopleId) {
            $qb->andWhere('p.id = :peopleId')
                ->setParameter('peopleId', $peopleId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findNewPeopleSince(\DateTimeInterface $since, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.enableAt is not null')
            ->andWhere('p.enableAt between :since and :now')
            ->andWhere('p.disabled = false')
            ->andWhere('p.hidden = false')
            ->setParameter('since', $since)
            ->setParameter('now', new \DateTime('now'))
            ->orderBy('p.enableAt', 'DESC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return People[]
     */
    public function searchActiveByName(string $query, int $limit = 10): array
    {
        $query = mb_trim($query);

        if ('' === $query) {
            return [];
        }

        $needle = '%'.mb_strtolower($query).'%';

        $qb = $this->createQueryBuilder('p');

        $qb
            ->where($qb->expr()->orX(
                $qb->expr()->like('LOWER(p.firstname)', ':needle'),
                $qb->expr()->like('LOWER(p.lastname)', ':needle'),
                $qb->expr()->like("LOWER(CONCAT(p.firstname, ' ', p.lastname))", ':needle'),
                $qb->expr()->like("LOWER(CONCAT(p.lastname, ' ', p.firstname))", ':needle'),
            ))
            ->andWhere('p.disabled = :false')
            ->andWhere('p.hidden = :false')
            ->setParameter('needle', $needle)
            ->setParameter('false', false)
            ->orderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->setMaxResults($limit)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findSubordinatesWithAnyFeatureName(
        People $user,
        array $featureNames,
        int $depth = 5,
        ?array $businessUnitIds = null
    ): array {
        if (empty($featureNames)) {
            return [];
        }

        $qb = $this->createQueryBuilder('subordinate');

        $qb
            ->select('DISTINCT subordinate')
            ->join('subordinate.acls', 'acl')
            ->join('acl.group', 'g')
            ->join('g.features', 'f')
            ->andWhere('subordinate.hidden = false')
            ->andWhere('subordinate.disabled = false')
            ->andWhere('f.name IN (:featureNames)')
            ->setParameter('featureNames', $featureNames);

        // Filter Business Unit optionnal
        if (!empty($businessUnitIds)) {
            $qb
                ->join('acl.location', 'aclLocation')
                ->join(
                    BusinessUnit::class,
                    'bu',
                    Join::WITH,
                    'bu.location = aclLocation.id'
                )
                ->andWhere('bu.id IN (:businessUnitIds)')
                ->setParameter('businessUnitIds', $businessUnitIds);
        }

        $orX = $qb->expr()->orX();
        $previousAlias = null;

        for ($level = 1; $level <= $depth; ++$level) {
            $alias = 'level'.$level;

            if (1 === $level) {
                $qb->leftJoin(
                    People::class,
                    $alias,
                    Join::WITH,
                    "$alias.id = subordinate.supervisor"
                );
            } else {
                $qb->leftJoin(
                    People::class,
                    $alias,
                    Join::WITH,
                    "$alias.id = $previousAlias.supervisor"
                );
            }

            $orX->add("$alias.id = :userId");
            $previousAlias = $alias;
        }

        $qb
            ->andWhere($orX)
            ->setParameter('userId', $user->getId());

        return $qb->getQuery()->getResult();
    }

    public function findByUsernameForLogin(string $username): ?People
    {
        $queryBuilder = $this->createQueryBuilder('p');

        return $queryBuilder
            ->addSelect('bu')
            ->addSelect('location')
            ->leftJoin('p.businessUnit', 'bu')
            ->leftJoin('bu.location', 'location')
            ->where('p.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return People[]
     */
    public function findAllMainAsmForCustomerHierarchy(Customer $customer): array
    {
        $asms = [];

        /** @var CustomerRepository $customerRepository */
        $customerRepository = $this->getEntityManager()->getRepository(Customer::class);

        foreach ($customerRepository->walkCustomerHierarchy($customer) as $current) {
            if (null !== $mainSalesRepresentative = $current->getMainSalesRepresentative()) {
                $asms[$mainSalesRepresentative->asm->getId()] = $mainSalesRepresentative->asm;
            }
        }

        return array_values($asms);
    }

    /**
     * @return People[]
     */
    public function findAllSecondaryAsmsForCustomerHierarchy(Customer $customer): array
    {
        $asms = [];

        /** @var CustomerRepository $customerRepository */
        $customerRepository = $this->getEntityManager()->getRepository(Customer::class);

        foreach ($customerRepository->walkCustomerHierarchy($customer) as $current) {
            foreach ($current->getSecondarySalesRepresentatives() as $secondarySalesRepresentative) {
                $asms[$secondarySalesRepresentative->asm->getId()] = $secondarySalesRepresentative->asm;
            }
        }

        return array_values($asms);
    }

    private function createByPositionByLocationQueryBuilder(
        Position $position,
        Location $location
    ): QueryBuilder {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.businessUnit', 'bu')
            ->leftJoin('bu.location', 'l')
            ->where('l = :location')
            ->andWhere('p.position = :position')
            ->andWhere('p.disabled = :false')
            ->andWhere('p.hidden = :false')
            ->setParameter('location', $location)
            ->setParameter('position', $position)
            ->setParameter('false', false);
    }
}
