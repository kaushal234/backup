<?php

declare(strict_types=1);

namespace AppBundle\Form\ChoiceList\Loader;

use Symfony\Component\Form\ChoiceList\Loader\AbstractChoiceLoader;

class AutoSubmittedChoiceLoader extends AbstractChoiceLoader
{
    public function loadChoicesForValues(array $values, ?callable $value = null): array
    {
        return $values;
    }

    protected function loadChoices(): iterable
    {
        return [];
    }
}
