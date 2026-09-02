<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Doctrine\Common\Filter\ExistsFilterInterface;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Util\RequestParser;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class StrictSearchListener implements EventSubscriberInterface
{
    /**
     * AuthorizationListener constructor.
     */
    public function __construct(
        private readonly ContainerInterface $filterLocator,
        private readonly array $allowedRouteParams = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['strictSearch', EventPriorities::PRE_READ]],
        ];
    }

    /**
     * @throws BadRequestHttpException when at least a filter is not enabled for the resource
     */
    public function strictSearch(RequestEvent $event)
    {
        $request = $event->getRequest();

        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        // We are only interested in collections
        if (!$request->attributes->has('_api_resource_class') || !$operation instanceof GetCollection) {
            return;
        }

        if ('' === ($queryString = RequestParser::getQueryString($request) ?? '')) {
            return;
        }

        $allowedPropertyName = $this->allowedRouteParams;
        $resourceClass = $request->attributes->get('_api_resource_class');
        // When the operation targets an underlying entity through stateOptions (e.g. a DTO
        // mapped from a Doctrine entity), filters describe themselves against that entity,
        // not against the non-managed resource class.
        $filterClass = $this->getStateOptionsClass($operation, $resourceClass);

        $resourceFilters = $operation->getFilters() ?? [];
        foreach ($resourceFilters as $filterId) {
            /** @var FilterInterface $filter */
            $filter = $this->filterLocator->get($filterId);
            if (!$filter instanceof FilterInterface) {
                continue;
            }

            foreach ($descriptions = array_keys($filter->getDescription($filterClass)) as $description) {
                $allowedPropertyName[] = $description;
            }

            // To allow legacy syntax waiting for the front to be migrated
            if ($filter instanceof ExistsFilterInterface) {
                foreach ($descriptions as $description) {
                    $allowedPropertyName[] = preg_replace('#^(exists)\[(.*)\]$#', '$2[$1]', $description);
                }
            }
        }

        foreach ($operation->getParameters() ?? [] as $queryParameter) {
            $allowedPropertyName[] = $queryParameter->getKey();
        }

        $requestParams = RequestParser::parseRequestParams($queryString);

        foreach (array_keys($this->flattenProperties($requestParams, '')) as $name) {
            if (\in_array($name, $allowedPropertyName, true) || \in_array($name.'[]', $allowedPropertyName, true)) {
                continue;
            }
            throw new BadRequestHttpException(\sprintf('The filter "%s" is not available for the resource "%s".', $name, $resourceClass));
        }
    }

    protected function flattenProperties(array $properties, $parent): array
    {
        if (
            ($properties && array_filter($properties, 'is_numeric', \ARRAY_FILTER_USE_KEY) === $properties)
            || array_keys($properties) === range(0, \count($properties) - 1)
        ) {
            return [$parent => $properties];
        }

        $flattenProperties = [];
        foreach ($properties as $property => $value) {
            if ('' === $parent) {
                $fullName = $property;
            } else {
                $fullName = is_numeric($property) ? $parent : \sprintf('%s[%s]', $parent, $property);
            }

            if (\is_array($value)) {
                $flattenProperties += $this->flattenProperties($value, $fullName);
            } else {
                $flattenProperties[$fullName] = $value;
            }
        }

        return $flattenProperties;
    }

    /**
     * Resolves the class against which filters describe themselves.
     *
     * When the operation maps a non-managed resource to a Doctrine entity through
     * stateOptions, filters are expressed against that underlying entity rather than
     * the resource class.
     *
     * @return class-string|null
     */
    private function getStateOptionsClass(Operation $operation, ?string $defaultClass): ?string
    {
        $options = $operation->getStateOptions();

        if ($options instanceof Options && ($entityClass = $options->getEntityClass())) {
            return $entityClass;
        }

        return $defaultClass;
    }
}
