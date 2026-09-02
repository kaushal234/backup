<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Workflow;
use Psr\Container\ContainerInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class WorkflowDataProvider implements ProviderInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            IriConverterInterface::class,
            Registry::class,
        ];
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (null === $iri = ($uriVariables['resource'] ?? null)) {
            return null;
        }

        try {
            $subject = $this->container->get(IriConverterInterface::class)->getResourceFromIri((string) $iri);
            $workflow = $this->container->get(Registry::class)->get($subject, $uriVariables['name'] ?: null);
        } catch (\Exception $exception) {
            return null;
        }

        $statuses = array_map(static fn (Transition $transition) => $transition->getTos(), $workflow->getEnabledTransitions($subject));

        return (new Workflow($iri))
            ->setAvailableStatuses([] !== $statuses ? array_unique(array_merge(...$statuses)) : [])
        ;
    }
}
