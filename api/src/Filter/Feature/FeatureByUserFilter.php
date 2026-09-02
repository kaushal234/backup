<?php

declare(strict_types=1);

namespace App\Filter\Feature;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FeatureByUserFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_USER_PROPERTY = 'user';
    /**
     * @var string
     */
    final public const FILTER_LOCATION_PROPERTY = 'location';

    private readonly IriConverterInterface $iriConverter;
    private readonly RequestStack $requestStack;

    public function __construct(IriConverterInterface $iriConverter, RequestStack $requestStack)
    {
        $this->iriConverter = $iriConverter;
        $this->requestStack = $requestStack;
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
        if (!$request->query->has(static::FILTER_USER_PROPERTY) && !$request->query->has(
            static::FILTER_LOCATION_PROPERTY
        )
        ) {
            return;
        }

        $queryBuilder
            ->innerJoin('o.groups', 'g')
            ->innerJoin('g.acls', 'a');

        if ($request->query->has(static::FILTER_USER_PROPERTY)) {
            $userValue = $this->iriConverter->getResourceFromIri($request->query->get(static::FILTER_USER_PROPERTY));
            $userParameter = $queryNameGenerator->generateParameterName(static::FILTER_USER_PROPERTY);

            $queryBuilder->andWhere(\sprintf('a.user = :%s', $userParameter))
                ->setParameter($userParameter, $userValue);
        }

        if ($request->query->has(static::FILTER_LOCATION_PROPERTY)) {
            $locationValue = $this->iriConverter->getResourceFromIri(
                $request->query->get(static::FILTER_LOCATION_PROPERTY)
            );
            $locationParameter = $queryNameGenerator->generateParameterName(static::FILTER_LOCATION_PROPERTY);

            $queryBuilder->andWhere(\sprintf('a.location = :%s', $locationParameter))
                ->setParameter($locationParameter, $locationValue);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_USER_PROPERTY => [
                'property' => static::FILTER_USER_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
            static::FILTER_LOCATION_PROPERTY => [
                'property' => static::FILTER_LOCATION_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
