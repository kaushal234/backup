<?php

declare(strict_types=1);

namespace App\Tests\Formatter\Snappy;

use App\Formatter\Snappy\Adapter;
use App\Formatter\Snappy\AdapterFactory\AdapterFactoryInterface;

class DummyAdapterFactory implements AdapterFactoryInterface
{
    private readonly string $supportedClass;

    private readonly string $usage;

    public function __construct(string $supportedClass, string $usage)
    {
        $this->supportedClass = $supportedClass;
        $this->usage = $usage;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapter($object, string $format): Adapter
    {
        return new Adapter('test.html.twig', ['param' => $object]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $format): bool
    {
        return $object::class === $this->supportedClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getPurpose(): string
    {
        return $this->usage;
    }
}
