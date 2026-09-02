<?php

declare(strict_types=1);

namespace App\AI\Handler;

use App\AI\Dto\Result;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @phpstan-type ApiSearchConfig array{class: class-string, fields: list<string>, route: string}
 */
final readonly class ApiSearchSourceHandler implements SearchSourceHandlerInterface
{
    /**
     * @param array<string, ApiSearchConfig> $configuration
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PropertyAccessorInterface $propertyAccessor,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
        #[Autowire(param: 'alvest_ai.search.api')]
        private array $configuration,
    ) {
    }

    public function supports(string $module): bool
    {
        return isset($this->configuration[$module]);
    }

    public function handle(array $data): ?Result
    {
        $config = $this->configuration[mb_strtolower($data['src'])];
        $repository = $this->entityManager->getRepository($config['class']);

        $source = $repository->find($data['id']);
        if (!$source) {
            return null;
        }

        return new Result(
            id: $data['id'],
            module: $data['src'],
            description: $this->buildDescription($source, $config['fields']),
            link: $this->urlGenerator->generate($config['route'], ['id' => $data['id']]),
        );
    }

    /**
     * @param list<string> $fields
     */
    private function buildDescription(object $source, array $fields): string
    {
        $description = '';

        foreach ($fields as $field) {
            try {
                $description .= $this->propertyAccessor->getValue($source, $field).' ';
            } catch (\Exception $exception) {
                $this->logger->error('Could not get description matching AI configuration. Reason : {reason}', ['reason' => $exception->getMessage()]);
            }
        }

        return mb_trim($description);
    }
}
