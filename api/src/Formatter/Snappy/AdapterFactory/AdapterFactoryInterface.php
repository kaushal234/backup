<?php

declare(strict_types=1);

namespace App\Formatter\Snappy\AdapterFactory;

use App\Formatter\Snappy\Adapter;
use App\Formatter\Snappy\EmptyDataException;

interface AdapterFactoryInterface
{
    /**
     * @param object $object
     *
     * @throws EmptyDataException
     */
    public function getAdapter($object, string $format): Adapter;

    /**
     * @param object $object
     */
    public function supports($object, string $format): bool;

    public function getPurpose(): string;
}
