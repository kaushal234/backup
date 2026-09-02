<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\Transformer;

use Twig\Environment;

class Templating
{
    private readonly Environment $twig;

    /**
     * Templating constructor.
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke($value, array $options)
    {
        if (!isset($options['template'])) {
            throw new \Exception('You should set the "template" option to use the "Templating" transformer.');
        }

        return $this->twig->render($options['template'], ['value' => $value]);
    }
}
