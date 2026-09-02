<?php

declare(strict_types=1);

namespace App\Filter\Support;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Support\Manual;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ManualPartDescriptionSearchFilter implements FilterInterface
{
    final public const FILTER_DESCRIPTION_PROPERTY = 'description';

    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(static::FILTER_DESCRIPTION_PROPERTY)) {
            return;
        }

        $value = $request->query->get(static::FILTER_DESCRIPTION_PROPERTY);

        if (Manual::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Manual resource');
        }

        if ('' === $value) {
            return;
        }

        $queryBuilder
            ->innerJoin('o.documents', 'd')
            ->innerJoin('d.parts', 'p')
            ->andWhere('MATCH(p.description, p.otherDescription) AGAINST(:value) > 0')
            ->setParameter('value', (string) $value)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_DESCRIPTION_PROPERTY => [
                'property' => static::FILTER_DESCRIPTION_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
