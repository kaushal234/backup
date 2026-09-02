<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Entity\Common\Subscription;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SubscriberFilter implements FilterInterface
{
    /**
     * @var string
     */
    private const FILTER_SUBSCRIBER_PROPERTY = 'subscribers';

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

        if (!$request->query->has(self::FILTER_SUBSCRIBER_PROPERTY)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $subscriptionAlias = $queryNameGenerator->generateJoinAlias('subscription');

        $iri = $this->iriConverter->getIriFromResource($resourceClass, UrlGeneratorInterface::ABS_PATH, new GetCollection());
        $queryBuilder->join(Subscription::class, $subscriptionAlias, Join::WITH, \sprintf("CONCAT('%s/', %s.id) = %s.resource", $iri, $rootAlias, $subscriptionAlias));

        $subscribers = (array) $request->query->get(self::FILTER_SUBSCRIBER_PROPERTY);
        $iriConverter = $this->iriConverter;
        $subscribers = array_map(static fn (string $subscriber) => $iriConverter->getResourceFromIri($subscriber), $subscribers);

        $parameter = $queryNameGenerator->generateParameterName(self::FILTER_SUBSCRIBER_PROPERTY);

        $queryBuilder
            ->andWhere(\sprintf('%s.user IN (:%s)', $subscriptionAlias, $parameter))
            ->setParameter($parameter, $subscribers)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            self::FILTER_SUBSCRIBER_PROPERTY => [
                'property' => self::FILTER_SUBSCRIBER_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
