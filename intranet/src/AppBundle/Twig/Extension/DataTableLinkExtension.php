<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DataTableLinkExtension extends AbstractExtension
{
    public function __construct(
        private readonly UrlGeneratorInterface $generator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('datatable_link', [$this, 'datatableLink']),
            new TwigFunction('datatable_params', [$this, 'datatableParams']),
        ];
    }

    public function datatableLink(string $route, string $type, ?array $filters = [], ?array $sort = []): string
    {
        $dataTableParameters = $this->datatableParams($type, $filters);

        foreach ($sort as $field => $direction) {
            $dataTableParameters[\sprintf('sort_%s', $type)][$field] = $direction;
        }

        return $this->generator->generate($route, $dataTableParameters);
    }

    public function datatableParams(string $type, ?array $filters = [])
    {
        $dataTableParameters = [];
        foreach ($filters as $filter => $value) {
            $dataTableParameters[\sprintf('filter_%s', $type)][$filter]['value'] = $value;
        }

        return $dataTableParameters;
    }
}
