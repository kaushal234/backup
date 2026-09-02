<?php

declare(strict_types=1);

namespace App\ApiPlatform\Provider;

use ApiPlatform\Metadata\Error as MetadataError;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ApiResource\Error;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: 'api_platform.state_provider.security_parameter')]
readonly class SecurityParameterProvider implements ProviderInterface
{
    public function __construct(
        #[AutowireDecorated]
        private ProviderInterface $decorated,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (null === $operation->getSecurity() && Error::class !== $operation->getClass() && !$operation instanceof MetadataError) {
            $operation = $operation->withSecurity("is_granted('ACCESS_PEOPLE')");
        }

        return $this->decorated->provide($operation, $uriVariables, $context);
    }
}
