<?php

declare(strict_types=1);

namespace App\Filter\MinutesOfMeeting;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Repository\Common\SubscriptionRepository;
use App\Util\Iri;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class MyMeetingFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_MY_MEETING = 'mine';

    private readonly RequestStack $requestStack;
    private readonly Security $security;
    private readonly SubscriptionRepository $subscriptionRepository;

    public function __construct(RequestStack $requestStack, Security $security, SubscriptionRepository $subscriptionRepository)
    {
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->subscriptionRepository = $subscriptionRepository;
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

        if (!$request->query->has(static::FILTER_MY_MEETING)) {
            return;
        }

        $value = $request->query->get(static::FILTER_MY_MEETING);

        /** @var People $user */
        $user = $this->security->getUser();

        if (null !== $user && \in_array($value, [true, 'true', '1'], true)) {
            $orStatements = $queryBuilder->expr()->orX();
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $attendeesAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'attendees', Join::LEFT_JOIN);

            $subscribedMeetings = $this->subscriptionRepository->findByResourceClassAndUser($user, Meeting::class);
            $subscribedMeetings = array_map(static fn (Subscription $subscription) => Iri::id($subscription->getResource()), $subscribedMeetings);

            $orStatements->add(\sprintf('%s.createdBy = :user', $rootAlias));
            $orStatements->add(\sprintf('%s.id = :user', $attendeesAlias));
            $orStatements->add(\sprintf('%s.id IN (:subscribedMeetings)', $rootAlias));

            $queryBuilder->andWhere($orStatements);

            $queryBuilder->setParameter('subscribedMeetings', $subscribedMeetings);
            $queryBuilder->setParameter('user', $user);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_MY_MEETING => [
                'property' => static::FILTER_MY_MEETING,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
