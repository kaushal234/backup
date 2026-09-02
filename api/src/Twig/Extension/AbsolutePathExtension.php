<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AbsolutePathExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('absolute_path', $this->getFileAbsolutePath(...), ['needs_environment' => true, 'is_safe' => ['all']]),
        ];
    }

    public function getFileAbsolutePath(Environment $env, $file): string
    {
        return $env->getLoader()->getSourceContext($file)->getPath();
    }
}
