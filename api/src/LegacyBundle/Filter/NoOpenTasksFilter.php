<?php

declare(strict_types=1);

namespace LegacyBundle\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Doctrine\Transformer\IriToModule;
use LegacyBundle\Manager\TaskManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class NoOpenTasksFilter implements FilterInterface
{
    /**
     * @var string
     */
    private const FILTER_NO_OPEN_TASKS_PROPERTY = 'noOpenTasks';

    private readonly RequestStack $requestStack;
    private readonly IriConverterInterface $iriConverter;
    private readonly IriToModule $iriToModule;
    private readonly TaskManager $taskManager;

    public function __construct(RequestStack $requestStack, IriConverterInterface $iriConverter, IriToModule $iriToModule, TaskManager $taskManager)
    {
        $this->requestStack = $requestStack;
        $this->iriConverter = $iriConverter;
        $this->iriToModule = $iriToModule;
        $this->taskManager = $taskManager;
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

        if (!$request->query->has(self::FILTER_NO_OPEN_TASKS_PROPERTY)) {
            return;
        }

        $iri = $this->iriConverter->getIriFromResource($resourceClass, UrlGeneratorInterface::ABS_PATH, new GetCollection());
        $iriToModule = $this->iriToModule;
        $module = $iriToModule($iri, []);

        $tasks = $this->taskManager->findOpenTasksByModule($module);

        if ([] === $tasks) {
            return;
        }

        $firstArticleQualificationIds = array_column($tasks, 'parent_id');

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $parameter = $queryNameGenerator->generateParameterName(self::FILTER_NO_OPEN_TASKS_PROPERTY);

        $queryBuilder
            ->andWhere(\sprintf('%s.id NOT IN (:%s)', $rootAlias, $parameter))
            ->setParameter($parameter, $firstArticleQualificationIds)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            self::FILTER_NO_OPEN_TASKS_PROPERTY => [
                'property' => self::FILTER_NO_OPEN_TASKS_PROPERTY,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
