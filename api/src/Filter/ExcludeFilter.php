<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use App\Util\Iri;
use Doctrine\ORM\QueryBuilder;
use http\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class ExcludeFilter implements FilterInterface
{
    /**
     * @var string
     */
    private const FILTER_EXCLUDE_NAME = 'exclude';

    /**
     * @var string
     */
    private const FILTER_EXCLUDE_PROPERTY_NAME = 'id';

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

        if (!$request->query->has(self::FILTER_EXCLUDE_NAME)) {
            return;
        }

        try {
            $item = $this->iriConverter->getResourceFromIri($request->query->get(self::FILTER_EXCLUDE_NAME), [AbstractObjectNormalizer::GROUPS => []]);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(\sprintf('Invalid resource submitted for filter %s', self::FILTER_EXCLUDE_NAME));
        }

        if (!$item instanceof $resourceClass) {
            return;
        }

        $queryBuilder
            ->andWhere(\sprintf('%s.id <> :id', $queryBuilder->getRootAliases()[0]))
            ->setParameter('id', Iri::id($request->query->get(self::FILTER_EXCLUDE_NAME)))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            self::FILTER_EXCLUDE_NAME => [
                'property' => self::FILTER_EXCLUDE_PROPERTY_NAME,
                'type' => 'integer',
                'required' => false,
            ],
        ];
    }
}
