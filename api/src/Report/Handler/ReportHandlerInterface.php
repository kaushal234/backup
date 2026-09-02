<?php

declare(strict_types=1);

namespace App\Report\Handler;

use App\Report\DataProvider\ReportDataProvider;

/**
 * This interface can be implemented when a specific report needs to be built
 * The DefaultHandler is in charge of handling all the requests sent to the Report resource item endpoint,
 * but it has the smallest priority, so custom handler would always be called first.
 *
 * @see DefaultHandler The standard handler
 * @see \App\Report\Report The API Resource
 */
interface ReportHandlerInterface
{
    /** @var string */
    public const METADATA_IRIS_X_KEY = 'xIris';
    /** @var string */
    public const METADATA_IRIS_Y_KEY = 'yIris';

    /**
     * @param string $resourceClass the uri of the collection of an ApiResource
     * @param string $x             the property path to use as grouping value for the X axis
     * @param string $y             the property path to use as grouping value for the Y axis
     * @param array  $options       the options array that can be filled with handler-specific key/value pairs
     */
    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider;

    public static function getDefaultPriority(): int;

    public function isGranted(?object $user = null): bool;
}
