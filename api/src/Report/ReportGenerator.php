<?php

declare(strict_types=1);

namespace App\Report;

use App\Report\Handler\ReportHandlerInterface;
use App\Routing\IriToClassnameConverter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ReportGenerator
{
    public function __construct(
        private readonly IriToClassnameConverter $converter,
        private readonly ReportDataExtractor $extractor,
        private readonly Security $security,
        private iterable $handlers = [],
    ) {
    }

    public function getReport(string $iri, string $x, string $y, array $options = []): Report
    {
        $resourceClass = $this->converter->convert($iri);
        $provider = null;

        $isGranted = false;
        $user = $this->security->getUser();
        foreach ($this->handlers as $handler) {
            if ($handler->isGranted($user)) {
                $isGranted = true;
            }
            if ($isGranted && null !== $provider = $handler->handle($resourceClass, $x, $y, $options)) {
                break;
            }
        }

        if (!$isGranted) {
            throw new AccessDeniedHttpException();
        }

        if (null === $provider) {
            throw new BadRequestHttpException();
        }

        $results = $this->extractor->extract($provider);
        $report = new Report($iri, $x, $y);

        foreach ($results as $row) {
            $row['extraData'] = \array_key_exists('extraData', $row) ? $row['extraData'] : [];
            $report->addCell($row['x'], $row['y'], (float) $row['value'], $row['extraData']);
        }

        foreach ($provider->provideMetadata() as $value => $metadata) {
            $report->addMetadata($value, $metadata);
        }

        if (isset($options['totalsAsAverages'])) {
            $report->renderTotalsAsAverages();
        }

        if ($provider->doCleanUp()) {
            $report->cleanUp();
        }

        return $report;
    }

    /**
     * @param iterable|ReportHandlerInterface[] $handlers
     */
    public function setHandlers(iterable $handlers): void
    {
        $this->handlers = $handlers;
    }
}
