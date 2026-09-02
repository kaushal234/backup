<?php

declare(strict_types=1);

namespace App\Repository\Legal;

use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\Legal\Category;
use App\Entity\Legal\Contract;
use App\Entity\Legal\SubCategory;
use App\Repository\Directory\PeopleRepository;
use App\Repository\FeatureRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

class ContractRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly FeatureRepository $featureRepository,
        private readonly Security $security,
        private readonly PeopleRepository $peopleRepository,
    ) {
        parent::__construct($registry, Contract::class);
    }

    public function getIdentifiersForSubCategory(SubCategory $subCategory): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.subCategory = :subCategory')
            ->setParameter('subCategory', $subCategory)
            ->getQuery()
            ->getArrayResult();
    }

    public function applyBusinessUnitRestrictions(QueryBuilder $queryBuilder, Orx $orStatement, People $people): void
    {
        $orStatement->add('bu.representative = :representative');
        $existingAliases = $queryBuilder->getAllAliases();

        if (!\in_array('representative_0', $existingAliases, true)) {
            $queryBuilder->leftJoin('bu.representative', 'representative_0');
        }

        $supervisor = $people->getSupervisor();
        $current = 'representative_0';

        for ($i = 0; $i <= 4; ++$i) {
            if (!$supervisor) {
                break;
            }

            $aliasNext = \sprintf('representative_%s', $i + 1);
            if (!\in_array($aliasNext, $existingAliases, true)) {
                $queryBuilder->leftJoin(\sprintf('%s.supervisor', $current), $aliasNext);
            }

            $orStatement->add(\sprintf('%s = :supervisor_%s', $aliasNext, $i));
            $queryBuilder->setParameter('supervisor_'.$i, $supervisor);

            $current = $aliasNext;
            $supervisor = $supervisor->getSupervisor();
        }

        $queryBuilder->setParameter('representative', $people);
    }

    public function applyDivisionRestrictions(QueryBuilder $queryBuilder, Orx $orStatement, People $people): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $existingAliases = $queryBuilder->getAllAliases();

        if (!\in_array('div', $existingAliases, true)) {
            $queryBuilder->leftJoin(\sprintf('%s.divisions', $rootAlias), 'div');
        }

        if (!\in_array('division_representative', $existingAliases, true)) {
            $queryBuilder->leftJoin('div.representatives', 'division_representative');
        }

        $orStatement->add('division_representative = :division_representative_people');
        $queryBuilder->setParameter('division_representative_people', $people);
    }

    public function applyCategoryFeaturesRestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people,
        array $featureNames
    ): void {
        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $grantedSubordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($grantedSubordinates)) {
            $peopleToLoadFeature = $isGranted ? $people : reset($grantedSubordinates);

            $features = $this->featureRepository->loadFeaturesByPeople($peopleToLoadFeature);

            $locations = array_column(
                array_filter($features, static fn (array $feature) => \in_array($feature['name'], $featureNames, true)),
                'location_id'
            );

            if (!empty($locations)) {
                if (!\in_array('l', $queryBuilder->getAllAliases(), true)) {
                    $queryBuilder->leftJoin('bu.location', 'l');
                }
                $orStatement->add('l.id IN (:locations)');
                $queryBuilder->setParameter('locations', $locations);
            }
        }
    }

    public function applyOwnerRestrictions(QueryBuilder $queryBuilder, Orx $orStatement, string $rootAlias, People $people): void
    {
        $orStatement->add(\sprintf('%s.owner = :owner', $rootAlias));
        $existingAliases = $queryBuilder->getAllAliases();

        if (!\in_array('owner_0', $existingAliases, true)) {
            $queryBuilder->leftJoin(\sprintf('%s.owner', $rootAlias), 'owner_0');
        }

        $current = 'owner_0';
        for ($i = 0; $i <= 4; ++$i) {
            $aliasNext = \sprintf('owner_%s', $i + 1);
            if (!\in_array($aliasNext, $existingAliases, true)) {
                $queryBuilder->leftJoin(\sprintf('%s.supervisor', $current), $aliasNext);
            }

            $orStatement->add(\sprintf('%s = :supervisor_%s', $aliasNext, $i));
            $queryBuilder->setParameter('supervisor_'.$i, $people);

            $current = $aliasNext;
        }

        $queryBuilder->setParameter('owner', $people);
    }

    public function applySubscriberRestrictions(QueryBuilder $queryBuilder, Orx $orStatement, string $rootAlias, People $people): void
    {
        $existingAliases = $queryBuilder->getAllAliases();

        if (!\in_array('subscription', $existingAliases, true)) {
            $queryBuilder->leftJoin(
                Subscription::class,
                'subscription',
                Join::WITH,
                \sprintf("CONCAT('/contracts/', %s.id) = subscription.resource", $rootAlias)
            );
        }

        $orStatement->add('subscription.user = :people');

        if (!\in_array('people_0', $existingAliases, true)) {
            $queryBuilder->leftJoin(People::class, 'people_0', Join::WITH, 'people_0 = subscription.user');
        }

        $current = 'people_0';
        for ($i = 0; $i <= 4; ++$i) {
            $aliasNext = \sprintf('people_%s', $i + 1);
            if (!\in_array($aliasNext, $existingAliases, true)) {
                $queryBuilder->leftJoin(\sprintf('%s.supervisor', $current), $aliasNext);
            }

            $orStatement->add(\sprintf('%s = :supervisor_%s', $aliasNext, $i));
            $queryBuilder->setParameter('supervisor_'.$i, $people);

            $current = $aliasNext;
        }

        $queryBuilder->setParameter('people', $people);
    }

    public function applyBankCategoryRestrictions(QueryBuilder $queryBuilder, string $rootAlias, People $people, bool $skipConfidential = false): Orx
    {
        $featureNames = [
            'FEATURE_CATEGORY_BANK_CONTRACT_ACCESS',
            'FEATURE_CATEGORY_BANK_CONTRACT_READ_ACCESS',
        ];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $subordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($subordinates)) {
            $orX = $queryBuilder->expr()
                ->orX()
                ->add('category.name = :bank_category');

            $queryBuilder->setParameter('bank_category', Category::BANK);

            return $orX;
        }

        $andX = $queryBuilder->expr()->andX();
        $orStatementUser = $queryBuilder->expr()->orX();

        $this->applyBusinessUnitRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyDivisionRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyCategoryFeaturesRestrictions($queryBuilder, $orStatementUser, $people, ['FEATURE_CATEGORY_BANK_CONTRACT_BU_ACCESS']);
        $this->applyOwnerRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);
        $this->applySubscriberRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);

        $andX->add('category.name = :bank_category');

        if (!$skipConfidential) {
            $andX->add(\sprintf('%s.confidential = :true', $rootAlias));
            $queryBuilder->setParameter('true', true);
        }

        $andX->add($orStatementUser);

        $queryBuilder->setParameter('bank_category', Category::BANK);

        $orStatement = $queryBuilder->expr()->orX();
        $orStatement->add($andX);

        return $orStatement;
    }

    public function applyITCategoryRestrictions(
        QueryBuilder $queryBuilder,
        string $rootAlias,
        People $people,
        bool $skipConfidential = false
    ): Orx {
        $featureNames = [
            'FEATURE_CATEGORY_IT_CONTRACT_ACCESS',
            'FEATURE_CATEGORY_IT_CONTRACT_READ_ACCESS',
        ];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $subordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($subordinates)) {
            $orX = $queryBuilder->expr()
                ->orX()
                ->add('category.name = :it_category');

            $queryBuilder->setParameter('it_category', Category::IP_IT);

            return $orX;
        }

        $andX = $queryBuilder->expr()->andX();
        $orStatementUser = $queryBuilder->expr()->orX();

        $this->applyBusinessUnitRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyDivisionRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyCategoryFeaturesRestrictions($queryBuilder, $orStatementUser, $people, ['FEATURE_CATEGORY_IT_CONTRACT_BU_ACCESS']);
        $this->applyOwnerRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);
        $this->applySubscriberRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);

        $andX->add('category.name = :it_category');

        if (!$skipConfidential) {
            $andX->add(\sprintf('%s.confidential = :true', $rootAlias));
            $queryBuilder->setParameter('true', true);
        }

        $andX->add($orStatementUser);

        $queryBuilder->setParameter('it_category', Category::IP_IT);

        $orStatement = $queryBuilder->expr()->orX();
        $orStatement->add($andX);

        return $orStatement;
    }

    public function applyMaCategoryRestrictions(
        QueryBuilder $queryBuilder,
        string $rootAlias,
        People $people,
        bool $skipConfidential = false
    ): Orx {
        $featureNames = [
            'FEATURE_CATEGORY_MA_CONTRACT_EDIT_ACCESS',
        ];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $subordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($subordinates)) {
            $orX = $queryBuilder->expr()
                ->orX()
                ->add('category.name = :ma_category');

            $queryBuilder->setParameter('ma_category', Category::MA);

            return $orX;
        }

        $andX = $queryBuilder->expr()->andX();
        $orStatementUser = $queryBuilder->expr()->orX();

        $this->applyOwnerRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);
        $this->applySubscriberRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);

        $andX->add('category.name = :ma_category');

        if (!$skipConfidential) {
            $andX->add(\sprintf('%s.confidential = :true', $rootAlias));
            $queryBuilder->setParameter('true', true);
        }

        $andX->add($orStatementUser);

        $queryBuilder->setParameter('ma_category', Category::MA);

        $orStatement = $queryBuilder->expr()->orX();
        $orStatement->add($andX);

        return $orStatement;
    }

    public function applyCustomerCategoryRestrictions(QueryBuilder $queryBuilder, string $rootAlias, People $people, bool $skipConfidential = false): Orx
    {
        $featureNames = [
            'FEATURE_CATEGORY_CUSTOMER_CONTRACT_ACCESS',
            'FEATURE_CATEGORY_CUSTOMER_CONTRACT_READ_ACCESS',
        ];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $subordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($subordinates)) {
            $orX = $queryBuilder->expr()
                ->orX()
                ->add('category.name = :customer_category');

            $queryBuilder->setParameter('customer_category', Category::CUSTOMERS);

            return $orX;
        }

        $andX = $queryBuilder->expr()->andX();
        $orStatementUser = $queryBuilder->expr()->orX();

        $this->applyBusinessUnitRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyDivisionRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyOwnerRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);
        $this->applySubscriberRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);

        // Customer ASM
        if (!\in_array('customer', $queryBuilder->getAllAliases(), true)) {
            $queryBuilder->leftJoin(\sprintf('%s.customers', $rootAlias), 'customer');
        }

        if (!\in_array('asm', $queryBuilder->getAllAliases(), true)) {
            $queryBuilder->leftJoin('customer.mainSalesRepresentative', 'msr');
            $queryBuilder->leftJoin('msr.asm', 'asm');
        }

        $current = 'asm';
        for ($i = 0; $i <= 4; ++$i) {
            $aliasNext = \sprintf('asm_%s', $i + 1);
            if (!\in_array($aliasNext, $queryBuilder->getAllAliases(), true)) {
                $queryBuilder->leftJoin(\sprintf('%s.supervisor', $current), $aliasNext);
            }

            $orStatementUser->add(\sprintf('%s = :customer_asm_%s', $current, $i));
            $queryBuilder->setParameter('customer_asm_'.$i, $people);

            $current = $aliasNext;
        }
        // End ASM

        $andX->add('category.name = :customer_category');

        if (!$skipConfidential) {
            $andX->add(\sprintf('%s.confidential = :true', $rootAlias));
            $queryBuilder->setParameter('true', true);
        }

        $andX->add($orStatementUser);

        $queryBuilder->setParameter('customer_category', Category::CUSTOMERS);

        $orStatement = $queryBuilder->expr()->orX();
        $orStatement->add($andX);

        return $orStatement;
    }

    public function applyRealEstateCategoryRestrictions(
        QueryBuilder $queryBuilder,
        string $rootAlias,
        People $people,
        bool $skipConfidential = false
    ): Orx {
        $featureNames = [
            'FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_READ_ACCESS',
        ];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $subordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($subordinates)) {
            $orX = $queryBuilder->expr()
                ->orX()
                ->add('category.name = :real_estate_category');

            $queryBuilder->setParameter('real_estate_category', Category::REAL_ESTATE);

            return $orX;
        }

        $andX = $queryBuilder->expr()->andX();
        $orStatementUser = $queryBuilder->expr()->orX();

        $this->applyBusinessUnitRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyDivisionRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyOwnerRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);
        $this->applySubscriberRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);
        $this->applyCategoryFeaturesRestrictions($queryBuilder, $orStatementUser, $people, ['FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_ACCESS']);
        $this->applyCategoryFeaturesRestrictions($queryBuilder, $orStatementUser, $people, ['FEATURE_CATEGORY_REAL_ESTATE_CONTRACT_BU_READ_ACCESS']);

        $subordinatesCFO = $this->peopleRepository->findSubordinatesWithAnyFeatureName(
            $people,
            ['ROLE_CFO', 'ROLE_LCM', 'ROLE_LGM']
        );

        if (!empty($subordinatesCFO)) {
            $orStatementUser->add('1 = 1');
        }

        $andX->add('category.name = :real_estate_category');

        if (!$skipConfidential) {
            $andX->add(\sprintf('%s.confidential = :true', $rootAlias));
            $queryBuilder->setParameter('true', true);
        }

        $andX->add($orStatementUser);

        $queryBuilder->setParameter('real_estate_category', Category::REAL_ESTATE);

        $orStatement = $queryBuilder->expr()->orX();
        $orStatement->add($andX);

        return $orStatement;
    }

    public function applyVendorsCategoryRestrictions(
        QueryBuilder $queryBuilder,
        string $rootAlias,
        People $people,
        bool $skipConfidential = false
    ): Orx {
        $featureNames = [
            'FEATURE_CATEGORY_VENDORS_CONTRACT_ACCESS',
            'FEATURE_CATEGORY_VENDORS_CONTRACT_READ_ACCESS',
        ];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $subordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($subordinates)) {
            $orX = $queryBuilder->expr()
                ->orX()
                ->add('category.name = :vendors_category');

            $queryBuilder->setParameter('vendors_category', Category::VENDORS);

            return $orX;
        }

        $andX = $queryBuilder->expr()->andX();
        $orStatementUser = $queryBuilder->expr()->orX();

        $this->applyBusinessUnitRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyDivisionRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyCategoryFeaturesRestrictions($queryBuilder, $orStatementUser, $people, ['FEATURE_CATEGORY_VENDORS_CONTRACT_BU_ACCESS']);
        $this->applyOwnerRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);
        $this->applySubscriberRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);

        $andX->add('category.name = :vendors_category');

        if (!$skipConfidential) {
            $andX->add(\sprintf('%s.confidential = :true', $rootAlias));
            $queryBuilder->setParameter('true', true);
        }

        $andX->add($orStatementUser);

        $queryBuilder->setParameter('vendors_category', Category::VENDORS);

        $orStatement = $queryBuilder->expr()->orX();
        $orStatement->add($andX);

        return $orStatement;
    }

    public function applyIntercoCategoryRestrictions(
        QueryBuilder $queryBuilder,
        string $rootAlias,
        People $people,
        bool $skipConfidential = false
    ): Orx {
        $featureNames = [
            'FEATURE_CATEGORY_INTERCO_CONTRACT_ACCESS',
        ];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $subordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);
        $subordinatesLGS = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, ['ROLE_LGS']);

        if ($isGranted || !empty($subordinates) || !empty($subordinatesLGS)) {
            $orX = $queryBuilder->expr()
                ->orX()
                ->add('category.name = :interco_category');

            $queryBuilder->setParameter('interco_category', Category::INTERCO);

            return $orX;
        }

        $andX = $queryBuilder->expr()->andX();
        $orStatementUser = $queryBuilder->expr()->orX();

        $this->applyBusinessUnitRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyDivisionRestrictions($queryBuilder, $orStatementUser, $people);
        $this->applyOwnerRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);
        $this->applySubscriberRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);
        $this->applyCategoryFeaturesRestrictions($queryBuilder, $orStatementUser, $people, ['FEATURE_CATEGORY_INTERCO_CONTRACT_BU_ACCESS']);
        $this->applyIntercoSubCategoryRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);

        $andX->add('category.name = :interco_category');

        if (!$skipConfidential) {
            $andX->add(\sprintf('%s.confidential = :true', $rootAlias));
            $queryBuilder->setParameter('true', true);
        }

        $andX->add($orStatementUser);

        $queryBuilder->setParameter('interco_category', Category::INTERCO);

        $orStatement = $queryBuilder->expr()->orX();
        $orStatement->add($andX);

        return $orStatement;
    }

    public function applyInsuranceCategoryRestrictions(
        QueryBuilder $queryBuilder,
        string $rootAlias,
        People $people,
        bool $skipConfidential = false
    ): Orx {
        $andX = $queryBuilder->expr()->andX();
        $orStatementUser = $queryBuilder->expr()->orX();

        $existingAliases = $queryBuilder->getAllAliases();

        if (!\in_array('subCategory', $existingAliases, true)) {
            $queryBuilder->leftJoin(\sprintf('%s.subCategory', $rootAlias), 'subCategory');
        }

        $this->applyOwnerRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);
        $this->applySubscriberRestrictions($queryBuilder, $orStatementUser, $rootAlias, $people);

        // Restrictions for BUILDING_PROPERTY_DAMAGES_MASTER_POLICY
        $this->applyInsuranceBuildingRestrictions($queryBuilder, $orStatementUser, $people);

        // Restrictions for CAR_LOCAL_POLICIES
        $this->applyInsuranceCarRestrictions($queryBuilder, $orStatementUser, $people);

        // Restrictions for DO_POLICIES
        $this->applyInsuranceDORestrictions($queryBuilder, $orStatementUser, $people);

        // Restrictions for GENERAL_LIABILITY
        $this->applyInsuranceGeneralLiabilityRestrictions($queryBuilder, $orStatementUser, $people);

        // Restrictions for AERO_LIABILITY
        $this->applyInsuranceAeroLiabilityRestrictions($queryBuilder, $orStatementUser, $people);

        // Restrictions for WORKER_COMPENSATION_LOCAL_POLICIES
        $this->applyInsuranceWorkerCompensationRestrictions($queryBuilder, $orStatementUser, $people);

        $andX->add('category.name = :insurance_category');

        if (!$skipConfidential) {
            $andX->add(\sprintf('%s.confidential = :true', $rootAlias));
            $queryBuilder->setParameter('true', true);
        }

        $andX->add($orStatementUser);

        $queryBuilder->setParameter('insurance_category', Category::INSURANCES);

        $orStatement = $queryBuilder->expr()->orX();
        $orStatement->add($andX);

        return $orStatement;
    }

    public function isReadableBy(Contract $contract, People $user): bool
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->select('1')
            ->leftJoin('c.businessUnits', 'bu')
            ->leftJoin('c.subCategory', 'subCategory')
            ->leftJoin('subCategory.category', 'category')
            ->where('c.id = :contractId')
            ->setParameter('contractId', $contract->getId())
        ;

        $orX = $queryBuilder->expr()->orX();
        $orX->add($this->applyBankCategoryRestrictions($queryBuilder, 'c', $user, true));
        $orX->add($this->applyCustomerCategoryRestrictions($queryBuilder, 'c', $user, true));
        $orX->add($this->applyITCategoryRestrictions($queryBuilder, 'c', $user, true));
        $orX->add($this->applyMaCategoryRestrictions($queryBuilder, 'c', $user, true));
        $orX->add($this->applyRealEstateCategoryRestrictions($queryBuilder, 'c', $user, true));
        $orX->add($this->applyVendorsCategoryRestrictions($queryBuilder, 'c', $user, true));
        $orX->add($this->applyIntercoCategoryRestrictions($queryBuilder, 'c', $user, true));
        $orX->add($this->applyInsuranceCategoryRestrictions($queryBuilder, 'c', $user, true));

        $queryBuilder->andWhere($orX)->setMaxResults(1);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findExpiredContracts(?\DateTimeInterface $referenceDate = null): array
    {
        $referenceDate ??= new \DateTime('today');

        return array_values(array_filter(
            $this->findExpirationCandidates(),
            static function (Contract $contract) use ($referenceDate): bool {
                $effective = $contract->getEffectiveExpirationDate();

                return null !== $effective && $effective < $referenceDate;
            }
        ));
    }

    public function findContractsExpiringInOneMonth(?\DateTimeInterface $referenceDate = null): array
    {
        $referenceDate ??= new \DateTime('today');
        $targetDate = (new \DateTimeImmutable())->setTimestamp($referenceDate->getTimestamp())->modify('+1 month');
        $nextDay = $targetDate->modify('+1 day');

        return array_values(array_filter(
            $this->findExpirationCandidates(),
            static function (Contract $contract) use ($targetDate, $nextDay): bool {
                $effective = $contract->getEffectiveExpirationDate();

                return null !== $effective && $effective >= $targetDate && $effective < $nextDay;
            }
        ));
    }

    /**
     * @return list<Contract>
     */
    private function findExpirationCandidates(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.subCategory', 'sc')
            ->where('c.status = :status')
            ->andWhere('c.indefinitePeriodType = :false')
            ->andWhere('c.expirationDate IS NOT NULL')
            ->andWhere('sc.name IS NULL OR sc.name != :excludedSubCategory')
            ->setParameter('status', Contract::ACTIVE)
            ->setParameter('false', false)
            ->setParameter('excludedSubCategory', SubCategory::EQUOTE_COMMERCIAL_OFFER)
            ->getQuery()
            ->getResult();
    }

    private function applyIntercoSubCategoryRestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        string $rootAlias,
        People $people
    ): void {
        $existingAliases = $queryBuilder->getAllAliases();

        if (!\in_array('subCategory', $existingAliases, true)) {
            $queryBuilder->leftJoin(\sprintf('%s.subCategory', $rootAlias), 'subCategory');
        }

        if ($this->security->isGranted('FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_ACCESS')) {
            $andXSub = $queryBuilder->expr()->andX();
            $andXSub->add('subCategory.name IN (:interco_sub_categories)');
            $queryBuilder->setParameter('interco_sub_categories', [
                SubCategory::CASH_POOLING_CONTRACT,
                SubCategory::MANAGEMENT_FEES_AGREEMENT,
            ]);
            $orStatement->add($andXSub);
        }

        $subordinatesGTD = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, ['ROLE_GTD']);
        if (!empty($subordinatesGTD)) {
            $andXSubGTD = $queryBuilder->expr()->andX();
            $andXSubGTD->add('subCategory.name IN (:interco_sub_categories_gtd)');
            $queryBuilder->setParameter('interco_sub_categories_gtd', [
                SubCategory::CASH_POOLING_CONTRACT,
                SubCategory::MANAGEMENT_FEES_AGREEMENT,
            ]);
            $orStatement->add($andXSubGTD);
        }

        $this->applyIntercoSubCategoryBURestrictions($queryBuilder, $orStatement, $people);
    }

    private function applyIntercoSubCategoryBURestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $featureNames = ['FEATURE_CATEGORY_INTERCO_CONTRACT_SUB_BU_ACCESS'];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $grantedSubordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($grantedSubordinates)) {
            $peopleToLoadFeature = $isGranted ? $people : reset($grantedSubordinates);

            $features = $this->featureRepository->loadFeaturesByPeople($peopleToLoadFeature);

            $locations = array_column(
                array_filter($features, static fn (array $feature) => \in_array($feature['name'], $featureNames, true)),
                'location_id'
            );

            if (!empty($locations)) {
                $existingAliases = $queryBuilder->getAllAliases();

                if (!\in_array('l', $existingAliases, true)) {
                    $queryBuilder->leftJoin('bu.location', 'l');
                }

                $andXSubBU = $queryBuilder->expr()->andX();
                $andXSubBU->add('l.id IN (:interco_sub_locations)');
                $andXSubBU->add('subCategory.name IN (:interco_sub_categories_bu)');

                $queryBuilder->setParameter('interco_sub_locations', $locations);
                $queryBuilder->setParameter('interco_sub_categories_bu', [
                    SubCategory::CASH_POOLING_CONTRACT,
                    SubCategory::MANAGEMENT_FEES_AGREEMENT,
                ]);

                $orStatement->add($andXSubBU);
            }
        }
    }

    private function applyInsuranceBuildingRestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        if ($this->security->isGranted('FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_ACCESS')) {
            $andXBuilding = $queryBuilder->expr()->andX();
            $andXBuilding->add('subCategory.name = :building_sub_category');
            $queryBuilder->setParameter('building_sub_category', SubCategory::BUILDING_PROPERTY_DAMAGES_MASTER_POLICY);
            $orStatement->add($andXBuilding);

            return;
        }

        $this->applyInsuranceBuildingBURestrictions($queryBuilder, $orStatement, $people);
        $this->applyInsuranceBuildingRepresentativeRestrictions($queryBuilder, $orStatement, $people);
        $this->applyInsuranceDivisionRepresentativeRestrictions(
            $queryBuilder,
            $orStatement,
            $people,
            SubCategory::BUILDING_PROPERTY_DAMAGES_MASTER_POLICY,
            'building'
        );

        $subordinatesCFO = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, ['ROLE_CFO']);
        if (!empty($subordinatesCFO)) {
            $andXBuildingCFO = $queryBuilder->expr()->andX();
            $andXBuildingCFO->add('subCategory.name = :building_cfo_sub_category');
            $queryBuilder->setParameter('building_cfo_sub_category', SubCategory::BUILDING_PROPERTY_DAMAGES_MASTER_POLICY);
            $orStatement->add($andXBuildingCFO);
        }
    }

    private function applyInsuranceBuildingBURestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $featureNames = ['FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_BU_ACCESS', 'FEATURE_SUB_CATEGORY_BUILDING_CONTRACT_BU_EDIT_ACCESS'];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $grantedSubordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($grantedSubordinates)) {
            $peopleToLoadFeature = $isGranted ? $people : reset($grantedSubordinates);

            $features = $this->featureRepository->loadFeaturesByPeople($peopleToLoadFeature);

            $locations = array_column(
                array_filter($features, static fn (array $feature) => \in_array($feature['name'], $featureNames, true)),
                'location_id'
            );

            if (!empty($locations)) {
                $existingAliases = $queryBuilder->getAllAliases();

                if (!\in_array('l', $existingAliases, true)) {
                    $queryBuilder->leftJoin('bu.location', 'l');
                }

                $andXBuildingBU = $queryBuilder->expr()->andX();
                $andXBuildingBU->add('l.id IN (:building_locations)');
                $andXBuildingBU->add('subCategory.name = :building_bu_sub_category');

                $queryBuilder->setParameter('building_locations', $locations);
                $queryBuilder->setParameter('building_bu_sub_category', SubCategory::BUILDING_PROPERTY_DAMAGES_MASTER_POLICY);

                $orStatement->add($andXBuildingBU);
            }
        }
    }

    private function applyInsuranceBuildingRepresentativeRestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $existingAliases = $queryBuilder->getAllAliases();

        if (!\in_array('bu', $existingAliases, true)) {
            $queryBuilder->leftJoin(\sprintf('%s.businessUnits', $queryBuilder->getRootAliases()[0]), 'bu');
        }

        $andXBuildingRep = $queryBuilder->expr()->andX();
        $andXBuildingRep->add('bu.representative = :building_representative');
        $andXBuildingRep->add('subCategory.name = :building_rep_sub_category');

        $queryBuilder->setParameter('building_representative', $people);
        $queryBuilder->setParameter('building_rep_sub_category', SubCategory::BUILDING_PROPERTY_DAMAGES_MASTER_POLICY);

        $orStatement->add($andXBuildingRep);
    }

    private function applyInsuranceCarRestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        if ($this->security->isGranted('FEATURE_SUB_CATEGORY_CAR_CONTRACT_ACCESS')) {
            $andXCar = $queryBuilder->expr()->andX();
            $andXCar->add('subCategory.name = :car_sub_category');
            $queryBuilder->setParameter('car_sub_category', SubCategory::CAR_LOCAL_POLICIES);
            $orStatement->add($andXCar);

            return;
        }

        $this->applyInsuranceCarBURestrictions($queryBuilder, $orStatement, $people);
        $this->applyInsuranceCarRepresentativeRestrictions($queryBuilder, $orStatement, $people);
        $this->applyInsuranceDivisionRepresentativeRestrictions(
            $queryBuilder,
            $orStatement,
            $people,
            SubCategory::CAR_LOCAL_POLICIES,
            'car'
        );

        $subordinatesCFO = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, ['ROLE_CFO']);
        if (!empty($subordinatesCFO)) {
            $andXCarCFO = $queryBuilder->expr()->andX();
            $andXCarCFO->add('subCategory.name = :car_cfo_sub_category');
            $queryBuilder->setParameter('car_cfo_sub_category', SubCategory::CAR_LOCAL_POLICIES);
            $orStatement->add($andXCarCFO);
        }
    }

    private function applyInsuranceCarBURestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $featureNames = ['FEATURE_SUB_CATEGORY_CAR_CONTRACT_BU_ACCESS', 'FEATURE_SUB_CATEGORY_CAR_CONTRACT_BU_EDIT_ACCESS'];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $grantedSubordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($grantedSubordinates)) {
            $peopleToLoadFeature = $isGranted ? $people : reset($grantedSubordinates);

            $features = $this->featureRepository->loadFeaturesByPeople($peopleToLoadFeature);

            $locations = array_column(
                array_filter($features, static fn (array $feature) => \in_array($feature['name'], $featureNames, true)),
                'location_id'
            );

            if (!empty($locations)) {
                $existingAliases = $queryBuilder->getAllAliases();

                if (!\in_array('l', $existingAliases, true)) {
                    $queryBuilder->leftJoin('bu.location', 'l');
                }

                $andXCarBU = $queryBuilder->expr()->andX();
                $andXCarBU->add('l.id IN (:car_locations)');
                $andXCarBU->add('subCategory.name = :car_bu_sub_category');

                $queryBuilder->setParameter('car_locations', $locations);
                $queryBuilder->setParameter('car_bu_sub_category', SubCategory::CAR_LOCAL_POLICIES);

                $orStatement->add($andXCarBU);
            }
        }
    }

    private function applyInsuranceCarRepresentativeRestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $existingAliases = $queryBuilder->getAllAliases();

        if (!\in_array('bu', $existingAliases, true)) {
            $queryBuilder->leftJoin(\sprintf('%s.businessUnits', $queryBuilder->getRootAliases()[0]), 'bu');
        }

        $andXCarRep = $queryBuilder->expr()->andX();
        $andXCarRep->add('bu.representative = :car_representative');
        $andXCarRep->add('subCategory.name = :car_rep_sub_category');

        $queryBuilder->setParameter('car_representative', $people);
        $queryBuilder->setParameter('car_rep_sub_category', SubCategory::CAR_LOCAL_POLICIES);

        $orStatement->add($andXCarRep);
    }

    private function applyInsuranceDORestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $featureNames = ['FEATURE_SUB_CATEGORY_DO_CONTRACT_ACCESS'];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $grantedSubordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($grantedSubordinates)) {
            $andXDO = $queryBuilder->expr()->andX();
            $andXDO->add('subCategory.name = :do_sub_category');
            $queryBuilder->setParameter('do_sub_category', SubCategory::DO_POLICIES);
            $orStatement->add($andXDO);
        }

        $subordinatesLGS = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, ['ROLE_LGS']);
        if (!empty($subordinatesLGS)) {
            $andXDOLGS = $queryBuilder->expr()->andX();
            $andXDOLGS->add('subCategory.name = :do_lgs_sub_category');
            $queryBuilder->setParameter('do_lgs_sub_category', SubCategory::DO_POLICIES);
            $orStatement->add($andXDOLGS);
        }
    }

    private function applyInsuranceGeneralLiabilityRestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $featureNames = ['FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_ACCESS'];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $grantedSubordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($grantedSubordinates)) {
            $andXGL = $queryBuilder->expr()->andX();
            $andXGL->add('subCategory.name = :general_liability_access_sub_category');
            $queryBuilder->setParameter('general_liability_access_sub_category', SubCategory::GENERAL_LIABILITY);
            $orStatement->add($andXGL);
        }

        $this->applyInsuranceGeneralLiabilityBURestrictions($queryBuilder, $orStatement, $people);

        $subordinatesLGS = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, ['ROLE_LGS']);
        if (!empty($subordinatesLGS)) {
            $andXGLLGS = $queryBuilder->expr()->andX();
            $andXGLLGS->add('subCategory.name = :general_liability_lgs_sub_category');
            $queryBuilder->setParameter('general_liability_lgs_sub_category', SubCategory::GENERAL_LIABILITY);
            $orStatement->add($andXGLLGS);
        }
    }

    private function applyInsuranceGeneralLiabilityBURestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $featureNames = ['FEATURE_SUB_CATEGORY_GENERAL_LIABILITY_CONTRACT_BU_ACCESS'];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $grantedSubordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($grantedSubordinates)) {
            $peopleToLoadFeature = $isGranted ? $people : reset($grantedSubordinates);

            $features = $this->featureRepository->loadFeaturesByPeople($peopleToLoadFeature);

            $locations = array_column(
                array_filter($features, static fn (array $feature) => \in_array($feature['name'], $featureNames, true)),
                'location_id'
            );

            if (!empty($locations)) {
                $existingAliases = $queryBuilder->getAllAliases();

                if (!\in_array('l', $existingAliases, true)) {
                    $queryBuilder->leftJoin('bu.location', 'l');
                }

                $andXGLBU = $queryBuilder->expr()->andX();
                $andXGLBU->add('l.id IN (:general_liability_locations)');
                $andXGLBU->add('subCategory.name = :general_liability_bu_sub_category');

                $queryBuilder->setParameter('general_liability_locations', $locations);
                $queryBuilder->setParameter('general_liability_bu_sub_category', SubCategory::GENERAL_LIABILITY);

                $orStatement->add($andXGLBU);
            }
        }
    }

    private function applyInsuranceAeroLiabilityRestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $featureNames = ['FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_ACCESS'];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $grantedSubordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($grantedSubordinates)) {
            $andXAero = $queryBuilder->expr()->andX();
            $andXAero->add('subCategory.name = :aero_liability_access_sub_category');
            $queryBuilder->setParameter('aero_liability_access_sub_category', SubCategory::AERO_LIABILITY);
            $orStatement->add($andXAero);
        }

        $this->applyInsuranceAeroLiabilityBURestrictions($queryBuilder, $orStatement, $people);

        $subordinatesLGS = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, ['ROLE_LGS']);
        if (!empty($subordinatesLGS)) {
            $andXAeroLGS = $queryBuilder->expr()->andX();
            $andXAeroLGS->add('subCategory.name = :aero_liability_lgs_sub_category');
            $queryBuilder->setParameter('aero_liability_lgs_sub_category', SubCategory::AERO_LIABILITY);
            $orStatement->add($andXAeroLGS);
        }
    }

    private function applyInsuranceAeroLiabilityBURestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $featureNames = ['FEATURE_SUB_CATEGORY_AERO_LIABILITY_CONTRACT_BU_ACCESS'];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $grantedSubordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($grantedSubordinates)) {
            $peopleToLoadFeature = $isGranted ? $people : reset($grantedSubordinates);

            $features = $this->featureRepository->loadFeaturesByPeople($peopleToLoadFeature);

            $locations = array_column(
                array_filter($features, static fn (array $feature) => \in_array($feature['name'], $featureNames, true)),
                'location_id'
            );

            if (!empty($locations)) {
                $existingAliases = $queryBuilder->getAllAliases();

                if (!\in_array('l', $existingAliases, true)) {
                    $queryBuilder->leftJoin('bu.location', 'l');
                }

                $andXAeroBU = $queryBuilder->expr()->andX();
                $andXAeroBU->add('l.id IN (:aero_liability_locations)');
                $andXAeroBU->add('subCategory.name = :aero_liability_bu_sub_category');

                $queryBuilder->setParameter('aero_liability_locations', $locations);
                $queryBuilder->setParameter('aero_liability_bu_sub_category', SubCategory::AERO_LIABILITY);

                $orStatement->add($andXAeroBU);
            }
        }
    }

    private function applyInsuranceWorkerCompensationRestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        if ($this->security->isGranted('FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_ACCESS')) {
            $andXWorker = $queryBuilder->expr()->andX();
            $andXWorker->add('subCategory.name = :worker_compensation_sub_category');
            $queryBuilder->setParameter('worker_compensation_sub_category', SubCategory::WORKER_COMPENSATION_LOCAL_POLICIES);
            $orStatement->add($andXWorker);

            return;
        }

        $this->applyInsuranceWorkerCompensationBURestrictions($queryBuilder, $orStatement, $people);
        $this->applyInsuranceWorkerCompensationRepresentativeRestrictions($queryBuilder, $orStatement, $people);
        $this->applyInsuranceDivisionRepresentativeRestrictions(
            $queryBuilder,
            $orStatement,
            $people,
            SubCategory::WORKER_COMPENSATION_LOCAL_POLICIES,
            'worker_compensation'
        );

        $subordinatesCFO = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, ['ROLE_CFO']);
        if (!empty($subordinatesCFO)) {
            $andXWorkerCFO = $queryBuilder->expr()->andX();
            $andXWorkerCFO->add('subCategory.name = :worker_compensation_cfo_sub_category');
            $queryBuilder->setParameter('worker_compensation_cfo_sub_category', SubCategory::WORKER_COMPENSATION_LOCAL_POLICIES);
            $orStatement->add($andXWorkerCFO);
        }
    }

    private function applyInsuranceWorkerCompensationBURestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $featureNames = ['FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_BU_ACCESS', 'FEATURE_SUB_CATEGORY_WORKER_COMPENSATION_CONTRACT_BU_EDIT_ACCESS'];

        $isGranted = false;
        foreach ($featureNames as $featureName) {
            if ($this->security->isGranted($featureName)) {
                $isGranted = true;
                break;
            }
        }

        $grantedSubordinates = $this->peopleRepository->findSubordinatesWithAnyFeatureName($people, $featureNames);

        if ($isGranted || !empty($grantedSubordinates)) {
            $peopleToLoadFeature = $isGranted ? $people : reset($grantedSubordinates);

            $features = $this->featureRepository->loadFeaturesByPeople($peopleToLoadFeature);

            $locations = array_column(
                array_filter($features, static fn (array $feature) => \in_array($feature['name'], $featureNames, true)),
                'location_id'
            );

            if (!empty($locations)) {
                $existingAliases = $queryBuilder->getAllAliases();

                if (!\in_array('l', $existingAliases, true)) {
                    $queryBuilder->leftJoin('bu.location', 'l');
                }

                $andXWorkerBU = $queryBuilder->expr()->andX();
                $andXWorkerBU->add('l.id IN (:worker_compensation_locations)');
                $andXWorkerBU->add('subCategory.name = :worker_compensation_bu_sub_category');

                $queryBuilder->setParameter('worker_compensation_locations', $locations);
                $queryBuilder->setParameter('worker_compensation_bu_sub_category', SubCategory::WORKER_COMPENSATION_LOCAL_POLICIES);

                $orStatement->add($andXWorkerBU);
            }
        }
    }

    private function applyInsuranceWorkerCompensationRepresentativeRestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people
    ): void {
        $existingAliases = $queryBuilder->getAllAliases();

        if (!\in_array('bu', $existingAliases, true)) {
            $queryBuilder->leftJoin(\sprintf('%s.businessUnits', $queryBuilder->getRootAliases()[0]), 'bu');
        }

        $andXWorkerRep = $queryBuilder->expr()->andX();
        $andXWorkerRep->add('bu.representative = :worker_compensation_representative');
        $andXWorkerRep->add('subCategory.name = :worker_compensation_rep_sub_category');

        $queryBuilder->setParameter('worker_compensation_representative', $people);
        $queryBuilder->setParameter('worker_compensation_rep_sub_category', SubCategory::WORKER_COMPENSATION_LOCAL_POLICIES);

        $orStatement->add($andXWorkerRep);
    }

    private function applyInsuranceDivisionRepresentativeRestrictions(
        QueryBuilder $queryBuilder,
        Orx $orStatement,
        People $people,
        string $subCategory,
        string $prefix
    ): void {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if (!\in_array('div', $queryBuilder->getAllAliases(), true)) {
            $queryBuilder->leftJoin(\sprintf('%s.divisions', $rootAlias), 'div');
        }

        $repAlias = $prefix.'_division_representative';
        if (!\in_array($repAlias, $queryBuilder->getAllAliases(), true)) {
            $queryBuilder->leftJoin('div.representatives', $repAlias);
        }

        $andX = $queryBuilder->expr()->andX();
        $andX->add(\sprintf('%s = :%s_param', $repAlias, $repAlias));
        $andX->add(\sprintf('subCategory.name = :%s_div_sub_category', $prefix));

        $queryBuilder->setParameter($repAlias.'_param', $people);
        $queryBuilder->setParameter($prefix.'_div_sub_category', $subCategory);

        $orStatement->add($andX);
    }
}
