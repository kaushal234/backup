<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ThirdPartyAppUpdateTaskExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (UpdateTask::class !== $resourceClass || $this->security->isGranted('FEATURE_MODULE_WRITE')) {
            return;
        }

        if (null === $user = $this->security->getUser()) {
            return;
        }

        if (!$user instanceof People) {
            return;
        }

        $thirdPartyAppId = $context['uri_variables']['thirdPartyAppId'] ?? null;

        // For findAll, apply filter on third party app ton only list those of which he is admin.
        if (null === $thirdPartyAppId) {
            $this->applyAdminFilter($queryBuilder, $user);

            return;
        }

        // Check if the user is admin of this third party app
        $thirdPartyApp = $this->entityManager->getRepository(Extended::class)->find($thirdPartyAppId);
        if (false === $this->security->isGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', $thirdPartyApp)) {
            throw new AccessDeniedHttpException();
        }
    }

    private function applyAdminFilter(QueryBuilder $queryBuilder, People $user): void
    {
        $thirdPartyApps = $this->entityManager->getRepository(Extended::class)->findThirdPartyAppsByAdmin($user);

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere($rootAlias.'.thirdPartyApp IN (:thirdPartyApps)');
        $queryBuilder->setParameter('thirdPartyApps', $thirdPartyApps);
    }
}
