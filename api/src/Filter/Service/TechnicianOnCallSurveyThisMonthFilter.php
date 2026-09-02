<?php

declare(strict_types=1);

namespace App\Filter\Service;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TechnicianOnCallSurveyThisMonthFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_USED_PROPERTY = 'surveyAnswerThisMonth';

    public function __construct(
        protected RequestStack $requestStack,
    ) {
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

        $hasSurveyThisMonth = $request->query->get(static::FILTER_USED_PROPERTY);

        if (null === $hasSurveyThisMonth) {
            return;
        }

        if (TechnicianOnCall::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the TechnicianOnCall resource');
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ('0' === $hasSurveyThisMonth || 'false' === $hasSurveyThisMonth || false === $hasSurveyThisMonth) {
            $queryBuilder
                ->leftJoin(\sprintf('%s.survey', $rootAlias), 'ts')
                ->andWhere('ts.id IS NULL')
            ;
        } else {
            $queryBuilder
                ->join(\sprintf('%s.survey', $rootAlias), 'ts')
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_USED_PROPERTY => [
                'property' => static::FILTER_USED_PROPERTY,
                'type' => 'boo',
                'required' => false,
            ],
        ];
    }
}
