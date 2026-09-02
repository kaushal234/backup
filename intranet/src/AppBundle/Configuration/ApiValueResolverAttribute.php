<?php

declare(strict_types=1);

namespace AppBundle\Configuration;

use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::IS_REPEATABLE)]
class ApiValueResolverAttribute extends ValueResolver
{
    /**
     * @param class-string<ValueResolverInterface>|string $resolver
     */
    public function __construct(
        public string $resolver = 'api_object',
        public bool $disabled = false,
        public array $parameters = [],
    ) {
        parent::__construct($this->resolver, $this->disabled);
    }
}
