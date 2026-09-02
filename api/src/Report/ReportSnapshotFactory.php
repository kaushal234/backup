<?php

declare(strict_types=1);

namespace App\Report;

use App\Entity\Report\ReportSnapshot;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReportSnapshotFactory
{
    private readonly NormalizerInterface $normalizer;
    private readonly DenormalizerInterface $denormalizer;

    public function __construct(NormalizerInterface $normalizer, DenormalizerInterface $denormalizer)
    {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
    }

    public function create(Report $report, array $options): ReportSnapshot
    {
        $normalizedReport = $this->normalizer->normalize($report, 'jsonld');

        $snapshot = $this->denormalizer->denormalize($normalizedReport, ReportSnapshot::class);
        $snapshot->options = $options;

        return $snapshot;
    }
}
