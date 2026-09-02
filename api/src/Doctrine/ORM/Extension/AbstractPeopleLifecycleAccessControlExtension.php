<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\Module\ThirdPartyApp\Member;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Restricts a people lifecycle collection (leavers / new comers) to people
 * linked to update tasks owned by modules the current user administrates,
 * when that user does not have the feature granted to HR & MIS.
 *
 * Only active for the targeted lifecycle query (identified by its query
 * parameter) so the rest of the /people API surface stays untouched.
 */
abstract class AbstractPeopleLifecycleAccessControlExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (People::class !== $resourceClass) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || !$request->query->has($this->getQueryParameter())) {
            return;
        }

        if ($this->security->isGranted($this->getRequiredFeature())) {
            return;
        }

        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof People) {
            $queryBuilder->andWhere('1 = 0');

            return;
        }

        $peopleAlias = $queryBuilder->getRootAliases()[0];

        $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder();
        $subQuery
            ->select('1')
            ->from(UpdateTask::class, 'updateTask')
            ->join('updateTask.thirdPartyApp', 'module')
            ->leftJoin(
                Member::class,
                'memberAdmin',
                'WITH',
                $subQuery->expr()->andX(
                    'memberAdmin.thirdPartyApp = module',
                    'memberAdmin.user = :currentUser',
                    'memberAdmin.admin = true'
                )
            )
            ->where('updateTask.user = '.$peopleAlias.'.id')
            ->andWhere(
                $subQuery->expr()->orX(
                    'module.mainAdmin = :currentUser',
                    'module.operationalOwner = :currentUser',
                    'memberAdmin.id IS NOT NULL'
                )
            );

        $queryBuilder
            ->andWhere($queryBuilder->expr()->exists($subQuery->getDQL()))
            ->setParameter('currentUser', $currentUser);
    }

    /**
     * The query parameter identifying the lifecycle collection this extension guards.
     */
    abstract protected function getQueryParameter(): string;

    /**
     * The feature granting unrestricted access to the lifecycle collection (HR & MIS).
     */
    abstract protected function getRequiredFeature(): string;
}
