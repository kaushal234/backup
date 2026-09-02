<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Audible\AudibleProvider;
use App\Doctrine\Mapping\Attributes\Audible;
use App\Doctrine\Voter\AuditLogVoter;
use App\Entity\AuditLog;
use App\Event\EntityChangeEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class AuditLogListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EntityChangeEvent::class => 'onEntityChange',
        ];
    }

    public function onEntityChange(EntityChangeEvent $event)
    {
        if (!$this->serviceLocator->get(AuditLogVoter::class)->vote()) {
            return;
        }

        $change = $event->getChange();

        $entity = $change->getEntity();
        $class = new \ReflectionClass($entity);

        if (empty($audibleAttributes = $class->getAttributes(Audible::class))) {
            return;
        }

        /** @var Audible $audible */
        $audible = $audibleAttributes[0]->newInstance();

        /** @var AudibleProvider $audibleProvider */
        $audibleProvider = $this->serviceLocator->get(AudibleProvider::class);
        $audibleConfiguration = $audibleProvider->getAudibleConfiguration($audible->type);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $repository = $entityManager->getRepository(AuditLog::class);

        $id = $this->serviceLocator->get(PropertyAccessorInterface::class)->getValue($entity, 'id');
        $count = 0;
        foreach ($change->getChangeSet() as $property => $propertyChangeSet) {
            if (\in_array($property, $audibleConfiguration->getAudibleProperties(), true)) {
                $auditLog = new AuditLog();
                $auditLog->auditType = $audible->type;
                $auditLog->property = $property;
                $auditLog->value = (string) $propertyChangeSet[1];
                $auditLog->referenceId = $id;
                $auditLog->createdAt = new \DateTime();
                $auditLog->createdBy = $this->serviceLocator->get(Security::class)->getUser();

                $previouses = $repository->findBy(['auditType' => $audible->type, 'referenceId' => $id, 'property' => $property], ['id' => 'DESC']);
                if (!empty($previouses)) {
                    $auditLog->setPrevious($previouses[0]);
                }

                $entityManager->persist($auditLog);
                ++$count;
            }
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            AuditLogVoter::class,
            AudibleProvider::class,
            EntityManagerInterface::class,
            PropertyAccessorInterface::class,
            Security::class,
        ];
    }
}
