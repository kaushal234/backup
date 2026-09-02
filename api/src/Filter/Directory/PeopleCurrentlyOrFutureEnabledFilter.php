<?php

declare(strict_types=1);

namespace App\Filter\Directory;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PeopleCurrentlyOrFutureEnabledFilter implements FilterInterface
{
    private const PROPERTY = 'peopleCurrentlyOrFutureEnabled';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (People::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the People resource');
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(self::PROPERTY)) {
            return;
        }

        // Expected URL:
        // /people?peopleCurrentlyOrFutureEnabled[enableAt]=YYYY-MM-DD&peopleCurrentlyOrFutureEnabled[plannedEnableAt]=YYYY-MM-DD
        $values = $request->query->all(self::PROPERTY);

        $enableAtStartRaw = $values['enableAt'] ?? null;
        $plannedEnableAtEndRaw = $values['plannedEnableAt'] ?? null;

        if (empty($enableAtStartRaw)) {
            throw new \Exception('This filter requires peopleCurrentlyOrFutureEnabled[enableAt]');
        }

        try {
            $enableAtStart = new \DateTime((string) $enableAtStartRaw);
            $plannedEnableAtEnd = !empty($plannedEnableAtEndRaw) ? new \DateTime((string) $plannedEnableAtEndRaw) : null;
        } catch (\Exception) {
            throw new \Exception('Invalid date value for peopleCurrentlyOrFutureEnabled filter.');
        }

        $now = new \DateTime('now');
        $alias = $queryBuilder->getRootAliases()[0];
        $expr = $queryBuilder->expr();
        $orConditions = [];

        // Already enabled
        // Some people have a null enableAt: they must be considered as already
        // enabled as long as they are not disabled.
        $orConditions[] = $expr->andX(
            $expr->eq($alias.'.disabled', 0),
            $expr->orX(
                $expr->isNull($alias.'.enableAt'),
                $expr->andX(
                    $expr->gte($alias.'.enableAt', ':enableAtStart'),
                    $expr->lte($alias.'.enableAt', ':now')
                )
            )
        );

        // Will arrive soon. Always added: scoping for users without the
        // unrestricted-access feature is handled upstream by
        // PeopleNewComersAccessControlExtension (module admins see only people
        // linked to their modules' update tasks).
        $plannedConditions = [
            $expr->eq($alias.'.disabled', 1),
            $expr->gte($alias.'.enableAt', ':now'),
        ];
        if (null !== $plannedEnableAtEnd) {
            $plannedConditions[] = $expr->lte($alias.'.enableAt', ':plannedEnableAtEnd');
            $queryBuilder->setParameter('plannedEnableAtEnd', $plannedEnableAtEnd);
        }
        $orConditions[] = $expr->andX(...$plannedConditions);

        $queryBuilder
            ->andWhere($expr->orX(...$orConditions))
            ->setParameter('now', $now)
            ->setParameter('enableAtStart', $enableAtStart);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY.'[enableAt]' => [
                'type' => 'string',
                'required' => true,
                'description' => 'Start date (inclusive) for already enabled people (enableAt >= this date).',
            ],
            self::PROPERTY.'[plannedEnableAt]' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Optional end date (inclusive) for people planned to arrive soon (enableAt <= this date). When omitted, there is no upper bound.',
            ],
        ];
    }
}
