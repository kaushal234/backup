<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Doctrine\Change;
use App\Doctrine\Mapping\Mapper\LoggingMapper;
use App\Doctrine\Voter\ActivityLogVoter;
use App\Entity\Activity\Log;
use App\Entity\AuthorizedApplication;
use App\Entity\User;
use App\Event\EntityChangeEvent;
use App\Exception\NotLoggableEntityException;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This listener create a new Log Entity when a change is triggered.
 */
class LogActionsListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
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

    /**
     * Logs changes.
     */
    public function onEntityChange(EntityChangeEvent $event)
    {
        if (!$this->serviceLocator->get(ActivityLogVoter::class)->vote()) {
            return;
        }

        $change = $event->getChange();

        $entity = $change->getEntity();
        $class = new \ReflectionClass($entity);
        $mapping = $this->serviceLocator->get(LoggingMapper::class)->getMapping($class);
        if (!\in_array($change->getAction(), $mapping['on'], true)) {
            return;
        }
        $changeSet = array_diff_key(
            $change->getChangeSet(),
            array_fill_keys($mapping['excluded_properties'], null)
        );

        foreach ($changeSet as $property => $propertyChangeSet) {
            if (\array_key_exists($property, $mapping['renamed_properties'])) {
                $changeSet[$this->serviceLocator->get(TranslatorInterface::class)->trans($mapping['renamed_properties'][$property], [], 'logs')] = $propertyChangeSet;
                unset($changeSet[$property]);
            }
            if (\array_key_exists($property, $mapping['datetime_properties_formats'])) {
                $format = $mapping['datetime_properties_formats'][$property];
                [$before, $after] = $propertyChangeSet;
                $changeSet[$property] = [
                    $before instanceof \DateTimeInterface ? $before->format($format) : $before,
                    $after instanceof \DateTimeInterface ? $after->format($format) : $after,
                ];
            }
        }

        if ($mapping['owner']) {
            if (isset($changeSet[$mapping['owner']])) {
                if (null !== $ownerEntity = $changeSet[$mapping['owner']][0]) {
                    $this->logOwnerDeletion($ownerEntity, $mapping['ownerRelation'], $entity);
                }
                if (null !== $ownerEntity = $changeSet[$mapping['owner']][1]) {
                    $this->logOwnerCreation($ownerEntity, $mapping['ownerRelation'], $entity);
                }
            } elseif (null !== $ownerEntity = $this->serviceLocator->get(PropertyAccessorInterface::class)->getValue($entity, $mapping['owner'])) {
                switch ($change->getAction()) {
                    case Change::ACTION_CREATE:
                        $this->logOwnerCreation($ownerEntity, $mapping['ownerRelation'], $entity);
                        break;
                    case Change::ACTION_DELETE:
                        $this->logOwnerDeletion($ownerEntity, $mapping['ownerRelation'], $entity);
                        break;
                    case Change::ACTION_UPDATE:
                        $relatedChangeSet = [];
                        $key = $mapping['ownerRelation'];
                        if ($mapping['showIri'] ?? false) {
                            try {
                                $key = $this->serviceLocator->get(IriConverterInterface::class)->getIriFromResource($entity);
                            } catch (\Exception $exception) {
                                // Do nothing
                            }
                        }

                        foreach ($changeSet as $field => $changes) {
                            $relatedChangeSet[$key.' => '.$field] = $changes;
                        }

                        $this->registerLog(
                            Change::ACTION_UPDATE,
                            $ownerEntity,
                            $relatedChangeSet
                        );
                        break;
                }
            }
        } else {
            $this->registerLog($change->getAction(), $entity, $changeSet);
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            ActivityLogVoter::class,
            IriConverterInterface::class,
            Security::class,
            ManagerRegistry::class,
            PropertyAccessorInterface::class,
            LoggingMapper::class,
            TranslatorInterface::class,
        ];
    }

    private function logOwnerDeletion($ownerEntity, $ownerRelation, $entity)
    {
        $this->registerLog(
            Change::ACTION_UPDATE,
            $ownerEntity,
            [$ownerRelation => [$entity, null]]
        );
    }

    private function logOwnerCreation($ownerEntity, $ownerRelation, $entity)
    {
        $this->registerLog(
            Change::ACTION_UPDATE,
            $ownerEntity,
            [$ownerRelation => [null, $entity]]
        );
    }

    private function registerLog(string $action, $entity, array $changeSet)
    {
        if (is_iterable($entity)) {
            foreach ($entity as $entityItem) {
                $this->registerLog($action, $entityItem, $changeSet);
            }

            return;
        }

        $changeSet = $this->cleanChangeSet($changeSet);

        if (Change::ACTION_UPDATE === $action && 0 === (is_countable($changeSet) ? \count($changeSet) : 0)) {
            return;
        }

        try {
            $iri = $this->serviceLocator->get(IriConverterInterface::class)->getIriFromResource($entity);
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw new NotLoggableEntityException($entity::class, $invalidArgumentException);
        }

        /** @var Log $log */
        $log = (new Log())
            ->setAction($action)
            ->setChangeSet($changeSet)
            ->setResource($iri);

        if (null !== $user = $this->serviceLocator->get(Security::class)->getUser()) {
            switch (true) {
                case $user instanceof User:
                    $log->setUser($user);
                    break;
                case $user instanceof AuthorizedApplication:
                    $log->setAuthorizedApplication($user);
                    break;
                default:
                    // do nothing
            }
        }

        $this->serviceLocator->get(ManagerRegistry::class)->getManager()->persist($log);
    }

    private function cleanChangeSet(array $changes)
    {
        $cleaned = [];
        foreach ($changes as $key => $change) {
            if ($change instanceof PersistentCollection) {
                continue;
            }

            if (\is_array($change)) {
                $cleanedChange = array_map($this->cleanChange(...), $change);
                [$before, $after] = $cleanedChange;
                if ([null, null] !== $cleanedChange && $before !== $after) {
                    $cleaned[$key] = $cleanedChange;
                }
            }
        }

        return $cleaned;
    }

    private function cleanChange($change)
    {
        if (\is_scalar($change)) {
            return $change;
        }

        if (\is_array($change)) {
            $parts = [];
            foreach ($change as $item) {
                $cleaned = $this->cleanChange($item);
                if (null !== $cleaned) {
                    $parts[] = (string) $cleaned;
                }
            }

            return implode(',', $parts);
        }

        if (\is_object($change)) {
            if ($change instanceof \DateTimeInterface) {
                return $change->format('Y-m-d H:i:s');
            }

            try {
                $iri = $this->serviceLocator->get(IriConverterInterface::class)->getIriFromResource($change);
            } catch (\Exception $exception) {
                $iri = null;
            }

            if (method_exists($change, '__toString')) {
                return null !== $iri ? \sprintf('%s (%s)', $change->__toString(), $iri) : $change->__toString();
            }

            if (null !== $iri) {
                return $iri;
            }
        }
    }
}
