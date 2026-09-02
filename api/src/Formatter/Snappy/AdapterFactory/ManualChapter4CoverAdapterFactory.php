<?php

declare(strict_types=1);

namespace App\Formatter\Snappy\AdapterFactory;

use App\Entity\Support\Manual;
use App\Formatter\Snappy\Adapter;

class ManualChapter4CoverAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @var string
     */
    final public const PURPOSE = 'pdf_chapter4_cover';

    /**
     * {@inheritdoc}
     *
     * @param Manual $manual
     */
    public function getAdapter($manual, string $format): Adapter
    {
        return new Adapter('Pdf/Support/Manual/Chapter4/cover.html.twig', ['manual' => $manual]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $format): bool
    {
        return $object instanceof Manual && self::PURPOSE === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getPurpose(): string
    {
        return self::PURPOSE;
    }
}
