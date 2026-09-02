<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension\Legal;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\Legal\Contract;
use App\Repository\Legal\ContractRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

readonly class ContractExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private Security $security,
        private ContractRepository $contractRepository
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (!$operation instanceof GetCollection) {
            return;
        }

        $this->apply($queryBuilder, $resourceClass, true);
    }

    public function apply(
        QueryBuilder $queryBuilder,
        string $resourceClass,
        bool $applyConfidential
    ): void {
        if (Contract::class !== $resourceClass) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof People) {
            $queryBuilder->andWhere('1=0');

            return;
        }

        // people not affect by the confidential check
        if (
            $this->security->isGranted('FEATURE_FULL_CONTRACT_ACCESS')
            || $this->security->isGranted('MKU_CRS')
            || $this->security->isGranted('MOO_CRS')
        ) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->leftJoin(\sprintf('%s.businessUnits', $rootAlias), 'bu')
            ->leftJoin(\sprintf('%s.subCategory', $rootAlias), 'subCategory')
            ->leftJoin('subCategory.category', 'category')
        ;

        $orX = $queryBuilder->expr()->orX();

        if ($applyConfidential) {
            $orX->add(\sprintf('%s.confidential = :false', $rootAlias));
            $queryBuilder->setParameter('false', false);
        }

        $bankRestriction = $this->contractRepository->applyBankCategoryRestrictions(
            $queryBuilder,
            $rootAlias,
            $user,
            !$applyConfidential // skipConfidential = true for item, false for collection
        );
        $orX->add($bankRestriction);

        $customerRestriction = $this->contractRepository->applyCustomerCategoryRestrictions(
            $queryBuilder,
            $rootAlias,
            $user,
            !$applyConfidential
        );
        $orX->add($customerRestriction);

        $itRestriction = $this->contractRepository->applyITCategoryRestrictions(
            $queryBuilder,
            $rootAlias,
            $user,
            !$applyConfidential
        );
        $orX->add($itRestriction);

        $maRestriction = $this->contractRepository->applyMaCategoryRestrictions(
            $queryBuilder,
            $rootAlias,
            $user,
            !$applyConfidential
        );
        $orX->add($maRestriction);

        $realEstateRestriction = $this->contractRepository->applyRealEstateCategoryRestrictions(
            $queryBuilder,
            $rootAlias,
            $user,
            !$applyConfidential
        );
        $orX->add($realEstateRestriction);

        $vendorsRestriction = $this->contractRepository->applyVendorsCategoryRestrictions(
            $queryBuilder,
            $rootAlias,
            $user,
            !$applyConfidential
        );
        $orX->add($vendorsRestriction);

        $intercoRestriction = $this->contractRepository->applyIntercoCategoryRestrictions(
            $queryBuilder,
            $rootAlias,
            $user,
            !$applyConfidential
        );
        $orX->add($intercoRestriction);

        $insuranceRestriction = $this->contractRepository->applyInsuranceCategoryRestrictions(
            $queryBuilder,
            $rootAlias,
            $user,
            !$applyConfidential
        );
        $orX->add($insuranceRestriction);

        $queryBuilder->andWhere($orX);
    }
}
