<?php

declare(strict_types=1);

namespace App\DataProvider;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Serializer\Exporter\Exporter;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\RequestStack;

#[FeatureDoc(path: 'export.md')]
#[AsDecorator(decorates: 'api_platform.doctrine.orm.state.collection_provider')]
final class SpreadsheetDataProvider implements ProviderInterface
{
    public function __construct(
        #[AutowireDecorated]
        private readonly ProviderInterface $provider,
        private readonly RequestStack $requestStack,
        private readonly Exporter $exporter,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$operation instanceof GetCollection) {
            return $this->provider->provide($operation, $uriVariables, $context);
        }

        if (!$this->exporter->isExportable($request)) {
            return $this->provider->provide($operation, $uriVariables, $context);
        }

        $collection = $this->provider->provide($operation->withForceEager(false), $uriVariables, $context);

        return $this->exporter->export(
            $operation,
            $collection,
            $request
        );
    }
}
