<?php

declare(strict_types=1);

namespace App\AI\Handler;

use App\AI\Dto\Result;
use LegacyBundle\Manager\CommonManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @phpstan-type LegacySearchConfig array{fields: list<string>, table: string, route: string, route_parameter: string, route_with_no_params: bool}
 */
final readonly class LegacySearchSourceHandler implements SearchSourceHandlerInterface
{
    /**
     * @param array<string, LegacySearchConfig> $configuration
     */
    public function __construct(
        private CommonManager $manager,
        private UrlGeneratorInterface $urlGenerator,
        #[Autowire(param: 'alvest_ai.search.legacy')]
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

        $source = $this->manager->getSource(
            $config['table'],
            $data['id'],
            $config['fields']
        );

        if ([] === $source) {
            return null;
        }

        $routeParameters = [
            'm' => $config['route_with_no_params'] ? ['view'] : [$config['route_parameter'], 'view'],
            'id' => $data['id'],
        ];

        return new Result(
            id: $data['id'],
            module: $data['src'],
            description: $this->buildDescription($source, $config['fields']),
            link: $this->urlGenerator->generate($config['route'], $routeParameters),
        );
    }

    /**
     * @param array<string, mixed> $source
     * @param list<string>         $fields
     */
    private function buildDescription(array $source, array $fields): string
    {
        return implode(' ', array_map(
            static fn (string $field) => $source[$field] ?? '',
            $fields
        ));
    }
}
