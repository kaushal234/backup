<?php

declare(strict_types=1);

namespace App\Filter\MinutesOfMeeting;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class MyTeamMeetingFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_MY_TEAM = 'myTeam';

    protected RequestStack $requestStack;
    private readonly PeopleRepository $peopleRepository;
    private readonly Security $security;

    public function __construct(RequestStack $requestStack, PeopleRepository $peopleRepository, Security $security)
    {
        $this->requestStack = $requestStack;
        $this->peopleRepository = $peopleRepository;
        $this->security = $security;
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

        if (!$request->query->has(static::FILTER_MY_TEAM)) {
            return;
        }

        $value = $request->query->get(static::FILTER_MY_TEAM);

        /** @var People $user */
        $user = $this->security->getUser();

        if (null !== $user && \in_array($value, [true, 'true', '1'], true)) {
            $subordinates = $this->peopleRepository->getSubordinates($user, 2);

            $orStatements = $queryBuilder->expr()->orX();

            $subordinatesId = array_reduce($subordinates, static function (array $memo, People $people) {
                $memo[] = $people->getId();

                return $memo;
            }, []);

            $rootAlias = $queryBuilder->getRootAliases()[0];
            $attendeesAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'attendees', Join::LEFT_JOIN);
            $creatorAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'createdBy', Join::LEFT_JOIN);

            $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.id', $attendeesAlias), ':subordinates'));
            $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.id', $creatorAlias), ':subordinates'));

            $queryBuilder->andWhere($orStatements);

            $queryBuilder->setParameter('subordinates', $subordinatesId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_MY_TEAM => [
                'property' => static::FILTER_MY_TEAM,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
