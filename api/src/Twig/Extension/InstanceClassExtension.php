<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class InstanceClassExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getTests(): array
    {
        return [
            new TwigTest('instanceOf', $this->isInstanceOf(...)),
        ];
    }

    public function isInstanceOf($var, $class): bool
    {
        return $var instanceof $class;
    }
}
