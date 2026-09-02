<?php

declare(strict_types=1);

namespace App\Audible;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AudibleProvider
{
    public function __construct(
        #[AutowireIterator(tag: 'app.audible')] private iterable $audibles,
    ) {
    }

    public function getAudibleConfiguration(string $type, ?string $property = null): AudibleInterface
    {
        /** @var AudibleInterface $audible */
        foreach ($this->audibles as $audible) {
            if (!$audible->supports($type)) {
                continue;
            }

            if (null !== $property && !\in_array($property, $audible->getAudibleProperties(), true)) {
                throw new UnprocessableEntityHttpException(\sprintf('Property %s not audible for class %s.', $property, $audible->getClass()));
            }

            return $audible;
        }

        throw new UnprocessableEntityHttpException('No Audible configuration found for this type.');
    }
}
