<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

class FiltersExtension extends AbstractExtension
{
    private readonly Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('filters', [$this, 'applyFilters'], ['needs_environment' => true]),
        ];
    }

    public function applyFilters(Environment $env, $value, $filters)
    {
        if (empty($filters)) {
            return $value;
        }
        if (\is_array($filters)) {
            $filters = implode('|', $filters);
        }

        $templateDirPath = $env->getCache().'/apply_filter';
        $templateFileName = str_replace(['/'], '', (string) $filters).'.html.twig';
        $templateFilePath = $templateDirPath.'/'.$templateFileName;

        if (!$this->filesystem->exists($templateFilePath)) {
            $this->filesystem->dumpFile($templateFilePath, \sprintf('{{ value|%s }}', $filters));
        }
        $oldLoader = $env->getLoader();

        $loader = new FilesystemLoader($templateDirPath);
        $env->setLoader($loader);

        $rendered = $env->render($templateFileName, ['value' => $value]);

        $env->setLoader($oldLoader);

        return $rendered;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'filters';
    }
}
