<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord\Survey;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class BuilderFactory
{
    public function __construct(
        #[AutowireIterator('app.survey.builder')]
        private readonly iterable $builders,
    ) {
    }

    public function getBuilder(array $data): ?AbstractBuilder
    {
        foreach ($this->builders as $builder) {
            if (!$builder->supports($data)) {
                continue;
            }

            return $builder;
        }

        return null;
    }
}
