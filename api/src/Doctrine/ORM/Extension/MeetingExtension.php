<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Action;
use App\Entity\MinutesOfMeeting\Meeting;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class MeetingExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (Meeting::class !== $resourceClass && Action::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        switch ($resourceClass) {
            default:
                return;
            case Meeting::class:
                $meetingAlias = $rootAlias;
                break;
            case Action::class:
                $meetingAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'meeting', Join::LEFT_JOIN);
                break;
        }

        $user = $this->container->get(Security::class)->getUser();
        if (!$user instanceof People) {
            $queryBuilder->where('1=0');

            return;
        }

        $posterAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $meetingAlias, 'createdBy', Join::LEFT_JOIN);
        $posterSupervisorAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $posterAlias, 'supervisor', Join::LEFT_JOIN);
        $attendeesAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $meetingAlias, 'attendees', Join::LEFT_JOIN);
        $orStatements = $queryBuilder->expr()->orX();
        $orStatements->add(\sprintf('%s.confidential = :confidential', $meetingAlias));
        $orStatements->add(\sprintf('%s.createdBy = :user', $meetingAlias));
        $orStatements->add(\sprintf('%s.supervisor = :user', $posterAlias));
        $orStatements->add(\sprintf('%s.supervisor = :user', $posterSupervisorAlias));
        $orStatements->add(\sprintf('%s.id = :user', $attendeesAlias));

        if (Action::class === $resourceClass) {
            $orStatements->add(\sprintf('%s.assignee = :user', $rootAlias));
            $assigneeSupervisorAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $rootAlias, 'assignee', Join::LEFT_JOIN);
            $orStatements->add(\sprintf('%s.supervisor = :user', $assigneeSupervisorAlias));
            $assigneeSupervisorSupervisorAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $assigneeSupervisorAlias, 'supervisor', Join::LEFT_JOIN);
            $orStatements->add(\sprintf('%s.supervisor = :user', $assigneeSupervisorSupervisorAlias));
        }

        $queryBuilder->setParameters(new ArrayCollection([
            new Parameter('confidential', false),
            new Parameter('user', $user),
        ]));
        $subscriptionAlias = $queryNameGenerator->generateJoinAlias('subscription');
        $queryBuilder->leftJoin(Subscription::class, $subscriptionAlias, Join::WITH, \sprintf("CONCAT('%s', %s.id) = %s.resource", '/minutes_of_meeting/meetings/', $meetingAlias, $subscriptionAlias));
        $orStatements->add(\sprintf('%s.user = :user', $subscriptionAlias));
        $queryBuilder->andWhere($orStatements);

        $queryBuilder->groupBy(\sprintf('%s.id', $rootAlias));
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}
