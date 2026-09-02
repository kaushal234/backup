<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use ApiPlatform\State\Util\RequestParser;
use App\Filter\ReportOptionsFilter;
use App\Report\Report;
use Symfony\Component\HttpFoundation\Request;

class ReportContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;

    public function __construct(SerializerContextBuilderInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (!$normalization || Report::class !== $context['resource_class']) {
            return $context;
        }

        $queryString = RequestParser::getQueryString($request);
        $filters = $queryString ? RequestParser::parseRequestParams($queryString) : [];

        // Passes the specific options to the ReportNormalizer
        $context += $filters[ReportOptionsFilter::PARAMETER_NAME] ?? [];

        return $context;
    }
}
