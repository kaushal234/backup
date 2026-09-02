<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Routing\IriToClassnameConverter;
use Symfony\Component\Workflow\Registry;

readonly class PlaceDataProvider implements ProviderInterface
{
    public function __construct(
        private IriToClassnameConverter $converter,
        private Registry $registry,
    ) {
    }

    /**
     * @return array<string>|null
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        try {
            $class = $this->converter->convert('/'.$uriVariables['iri']);
            $workflow = $this->registry->get(new $class());
        } catch (\Exception) {
            return null;
        }

        return $workflow->getDefinition()->getPlaces();
    }
}
