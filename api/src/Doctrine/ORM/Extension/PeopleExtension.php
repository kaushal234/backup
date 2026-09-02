<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class PeopleExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    final public const BUYERS_OPERATION_NAME = 'buyers';
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
        if (People::class !== $resourceClass) {
            return;
        }

        if (self::BUYERS_OPERATION_NAME !== $operation->getName()) {
            return;
        }

        if (null === $user = $this->container->get(Security::class)->getUser()) {
            return;
        }

        if (!$user instanceof VendorUser) {
            return;
        }

        if ($user->contact->getBusinessPartners()->isEmpty()) {
            $queryBuilder->where('1=0');

            return;
        }

        $emails = [];
        /** @var BusinessPartner $businessPartner */
        foreach ($user->contact->getBusinessPartners() as $businessPartner) {
            foreach ($businessPartner->getBuyFromDepartments() as $department) {
                if (null === $department->buyer) {
                    continue;
                }

                $emails[] = $department->buyer->emailAddress;
            }
        }

        $emails = array_filter($emails);

        $queryBuilder
            ->andWhere($queryBuilder->expr()->in(\sprintf('%s.email', $queryBuilder->getRootAliases()[0]), ':buyers'))
            ->setParameter('buyers', $emails)
        ;
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}
