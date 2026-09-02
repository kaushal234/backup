<?php

declare(strict_types=1);

namespace App\Report\DataProvider\Extractor;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Report\Handler\ReportHandlerInterface;

class IrisExtractorBuilder
{
    private ?string $xClass = null;
    private ?string $xField = null;
    private ?string $yClass = null;
    private ?string $yField = null;

    private readonly IriConverterInterface $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    public function setX(string $xClass, string $xField): self
    {
        $this->xClass = $xClass;
        $this->xField = $xField;

        return $this;
    }

    public function setY(string $yClass, string $yField): self
    {
        $this->yClass = $yClass;
        $this->yField = $yField;

        return $this;
    }

    public function generate(): callable
    {
        $xClassIri = null;
        if (null !== $this->xClass) {
            $xClassIri = $this->iriConverter->getIriFromResource($this->xClass, UrlGeneratorInterface::ABS_PATH, new GetCollection());
        }

        $yClassIri = null;
        if (null !== $this->yClass) {
            $yClassIri = $this->iriConverter->getIriFromResource($this->yClass, UrlGeneratorInterface::ABS_PATH, new GetCollection());
        }

        if (null === $xClassIri && null === $yClassIri) {
            throw new \InvalidArgumentException('At least x or y class iri should be set');
        }

        return function ($results) use ($xClassIri, $yClassIri) {
            $xIris = [];
            $yIris = [];

            foreach ($results as $result) {
                if (null !== $xClassIri) {
                    $xIris[$result['x']] = \sprintf('%s/%s', $xClassIri, $result[$this->xField]);
                }

                if (null !== $yClassIri) {
                    $yIris[$result['y']] = \sprintf('%s/%s', $yClassIri, $result[$this->yField]);
                }
            }

            return [
                ReportHandlerInterface::METADATA_IRIS_X_KEY => $xIris,
                ReportHandlerInterface::METADATA_IRIS_Y_KEY => $yIris,
            ];
        };
    }
}
