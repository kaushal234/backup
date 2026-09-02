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

final class PeopleCurrentlyOrFutureDisabledFilter implements FilterInterface
{
    private const PROPERTY = 'peopleCurrentlyOrFutureDisabled';

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
        // /people?peopleCurrentlyOrFutureDisabled[disabledAt]=YYYY-MM-DD&peopleCurrentlyOrFutureDisabled[plannedDisableAt]=YYYY-MM-DD
        $values = $request->query->all(self::PROPERTY);

        $disabledAtStartRaw = $values['disabledAt'] ?? null;
        $plannedDisableAtEndRaw = $values['plannedDisableAt'] ?? null;

        if (empty($disabledAtStartRaw)) {
            throw new \Exception('This filter requires peopleCurrentlyOrFutureDisabled[disabledAt]');
        }

        try {
            $disabledAtStart = new \DateTime((string) $disabledAtStartRaw);
            $plannedDisableAtEnd = !empty($plannedDisableAtEndRaw) ? new \DateTime((string) $plannedDisableAtEndRaw) : null;
        } catch (\Exception) {
            throw new \Exception('Invalid date value for peopleCurrentlyOrFutureDisabled filter.');
        }

        $now = new \DateTime('now');
        $alias = $queryBuilder->getRootAliases()[0];
        $expr = $queryBuilder->expr();
        $orConditions = [];

        // Already disabled
        $orConditions[] = $expr->andX(
            $expr->eq($alias.'.disabled', 1),
            $expr->gte($alias.'.disabledAt', ':disabledAtStart'),
            $expr->lte($alias.'.disabledAt', ':now')
        );

        // Will be disabled soon. Always added: scoping for users without the
        // unrestricted-access feature is handled upstream by
        // PeopleLeaversAccessControlExtension (module admins see only people
        // linked to their modules' update tasks).
        $plannedConditions = [
            $expr->eq($alias.'.disabled', 0),
            $expr->gte($alias.'.plannedDisableAt', ':now'),
        ];
        if (null !== $plannedDisableAtEnd) {
            $plannedConditions[] = $expr->lte($alias.'.plannedDisableAt', ':plannedDisableAtEnd');
            $queryBuilder->setParameter('plannedDisableAtEnd', $plannedDisableAtEnd);
        }
        $orConditions[] = $expr->andX(...$plannedConditions);

        $queryBuilder
            ->andWhere($expr->orX(...$orConditions))
            ->setParameter('now', $now)
            ->setParameter('disabledAtStart', $disabledAtStart);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY.'[disabledAt]' => [
                'type' => 'string',
                'required' => true,
                'description' => 'Start date (inclusive) for already disabled people (disabledAt >= this date).',
            ],
            self::PROPERTY.'[plannedDisableAt]' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Optional end date (inclusive) for people planned to be disabled soon (plannedDisableAt <= this date). When omitted, there is no upper bound.',
            ],
        ];
    }
}
