<?php

declare(strict_types=1);

namespace App\Formatter\Snappy;

use App\Formatter\Snappy\AdapterFactory\AdapterFactoryInterface;
use Knp\Snappy\GeneratorInterface;
use Twig\Environment;

class Formatter implements FormatterInterface
{
    private readonly Environment $twig;

    private readonly GeneratorInterface $converter;

    private readonly iterable $adapterFactories;

    /**
     * PdfFormatter constructor.
     */
    public function __construct(Environment $twig, GeneratorInterface $converter, iterable $adapters)
    {
        $this->twig = $twig;
        $this->converter = $converter;
        $this->adapterFactories = $adapters;
    }

    public function convert($object, $purpose, string $format, array $extraContext = []): string
    {
        $adapter = $this->getAdapter($object, $purpose, $format);

        foreach ($extraContext as $key => $value) {
            $adapter->addToContext($key, $value);
        }

        $html = $this->twig->render($adapter->getTemplate(), $adapter->getContext());

        $options = $adapter->getOptions();

        if (null !== $adapter->getHeaderTemplate()) {
            $header = $this->twig->render($adapter->getHeaderTemplate(), $adapter->getContext());
            $options += ['header-html' => $header, 'header-spacing' => 5];
        }
        if (null !== $adapter->getFooterTemplate()) {
            $footer = $this->twig->render($adapter->getFooterTemplate(), $adapter->getContext());
            $options += ['footer-html' => $footer];
        }

        return $this->converter->getOutputFromHtml(
            $html, $options
        );
    }

    private function getAdapter($object, string $purpose, string $format): Adapter
    {
        foreach ($this->adapterFactories as $adapter) {
            /** @var AdapterFactoryInterface $adapter */
            if ($adapter->getPurpose() === $purpose && $adapter->supports($object, $format)) {
                return $adapter->getAdapter($object, $format);
            }
        }

        throw new \InvalidArgumentException(\sprintf('No adapter found for %s and class %s on format %s', $purpose, $object::class, $format));
    }
}
