<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Twig\Extension;

use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

use function is_array;
use function sprintf;

class FiltersExtension extends AbstractExtension
{
    /** @var Filesystem */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return array<object>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('filters', [$this, 'applyFilters'], ['needs_environment' => true]),
        ];
    }

    /**
     * @param string                        $value
     * @param string|resource|array<string> $filters
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function applyFilters(Environment $env, $value, $filters): string
    {
        if (empty($filters)) {
            return $value;
        }
        if (is_array($filters)) {
            $filters = implode('|', $filters);
        }

        $templateDirPath = $env->getCache().'/apply_filter';
        $templateFileName = str_replace(['/'], '', $filters).'.html.twig';
        $templateFilePath = $templateDirPath.'/'.$templateFileName;

        if (!$this->filesystem->exists($templateFilePath)) {
            $this->filesystem->dumpFile($templateFilePath, sprintf('{{ value|%s }}', $filters));
        }
        $oldLoader = $env->getLoader();

        $loader = new FilesystemLoader($templateDirPath);
        $env->setLoader($loader);

        $rendered = $env->render($templateFileName, ['value' => $value]);

        $env->setLoader($oldLoader);

        return $rendered;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'filters';
    }
}
