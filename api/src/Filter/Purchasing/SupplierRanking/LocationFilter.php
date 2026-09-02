<?php

declare(strict_types=1);

namespace App\Filter\Purchasing\SupplierRanking;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Repository\Directory\LocationRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * This filter add a default filter on location, depending on user feature FEATURE_SUPPLIER_RANKING_READ.
 * If the user has feature FEATURE_SUPPLIER_RANKING_READ_ALL, he has access to all datas.
 * We can also add a filter on request params.
 */
class LocationFilter implements FilterInterface
{
    final public const FILTER_LOCATION_PROPERTY = 'location';
    private readonly Security $security;

    private readonly LocationRepository $locationRepository;

    private readonly IriConverterInterface $iriConverter;

    private readonly RequestStack $requestStack;

    public function __construct(Security $security, LocationRepository $locationRepository, RequestStack $requestStack, IriConverterInterface $iriConverter)
    {
        $this->security = $security;
        $this->locationRepository = $locationRepository;
        $this->iriConverter = $iriConverter;
        $this->requestStack = $requestStack;
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        if (SupplierRanking::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to supplier ranking');
        }

        // Get filter optionnal location if param has been set.
        $filteredLocation = null;
        if ($request->query->has(static::FILTER_LOCATION_PROPERTY)) {
            $filteredLocation = $this->iriConverter->getResourceFromIri(
                $request->query->get(static::FILTER_LOCATION_PROPERTY)
            );
        }

        // Apply default filter for user does not have acces to all supplier ranking.
        if (!$this->security->isGranted('FEATURE_SUPPLIER_RANKING_READ_ALL')) {
            $userLocations = $this->locationRepository->findByUserAndFeatures($this->security->getUser(), ['FEATURE_SUPPLIER_RANKING_READ']);

            // Check if the optionnal filter is in the location list of user. If no, use default filter.
            if (!\in_array($filteredLocation, $userLocations, true)) {
                $filteredLocation = $userLocations;
            }
        }

        if ($filteredLocation) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->innerJoin(\sprintf('%s.supplier', $rootAlias), 's');
            $queryBuilder->andWhere('s.location IN (:locations)');
            $queryBuilder->setParameter('locations', $filteredLocation);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'location' => [
                'property' => 'location',
                'type' => TypeIdentifier::NULL->value,
                'required' => false,
            ],
        ];
    }
}
