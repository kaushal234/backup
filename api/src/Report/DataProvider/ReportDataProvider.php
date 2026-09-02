<?php

declare(strict_types=1);

namespace App\Report\DataProvider;

class ReportDataProvider
{
    private readonly array $dataExtractor;

    private readonly ?array $xLabelsExtractor;

    private readonly ?array $yLabelsExtractor;

    /** @var callable|null */
    private $metadataExtractor;

    private bool $doCleanUp;

    public function __construct(
        array $dataExtractor,
        ?array $xLabelsExtractor = null,
        ?array $yLabelsExtractor = null,
        bool $doCleanUp = true
    ) {
        $this->dataExtractor = $dataExtractor;
        $this->xLabelsExtractor = $xLabelsExtractor;
        $this->yLabelsExtractor = $yLabelsExtractor;
        $this->doCleanUp = $doCleanUp;
    }

    public function provideData(): array
    {
        return $this->dataExtractor;
    }

    public function provideXLabels(): ?array
    {
        return $this->xLabelsExtractor;
    }

    public function provideYLabels(): ?array
    {
        return $this->yLabelsExtractor;
    }

    public function provideMetadata(): array
    {
        $extractor = $this->metadataExtractor;

        return null !== $extractor ? $extractor($this->dataExtractor) : [];
    }

    public function setMetadataExtractor(callable $metadataExtractor): self
    {
        $this->metadataExtractor = $metadataExtractor;

        return $this;
    }

    public function doCleanUp(): bool
    {
        return $this->doCleanUp;
    }
}
