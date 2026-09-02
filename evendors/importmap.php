<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap' => [
        'version' => '5.3.6',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.6',
        'type' => 'css',
    ],
    'bootstrap-datepicker' => [
        'version' => '1.10.0',
    ],
    'bootstrap-datepicker/dist/css/bootstrap-datepicker3.css' => [
        'version' => '1.10.1',
        'type' => 'css',
    ],
    'datatables.net-bs5' => [
        'version' => '2.3.0',
    ],
    'datatables.net' => [
        'version' => '2.3.0',
    ],
    'datatables.net-bs5/css/dataTables.bootstrap5.min.css' => [
        'version' => '2.3.0',
        'type' => 'css',
    ],
    'datatables.net-searchpanes-bs5' => [
        'version' => '2.3.3',
    ],
    'datatables.net-searchpanes' => [
        'version' => '2.3.3',
    ],
    'datatables.net-searchpanes-bs5/css/searchPanes.bootstrap5.min.css' => [
        'version' => '2.3.3',
        'type' => 'css',
    ],
    'datatables.net-select-bs5' => [
        'version' => '2.1.0',
    ],
    'datatables.net-select' => [
        'version' => '2.1.0',
    ],
    'datatables.net-select-bs5/css/select.bootstrap5.min.css' => [
        'version' => '2.1.0',
        'type' => 'css',
    ],
    'highcharts' => [
        'version' => '11.4.8',
    ],
    'highcharts/highcharts-more' => [
        'version' => '11.4.8',
    ],
    'highcharts/modules/exporting' => [
        'version' => '11.4.8',
    ],
    'axios' => [
        'version' => '1.15.1',
    ],
    '@fortawesome/fontawesome-free' => [
        'version' => '6.6.0',
    ],
    '@fortawesome/fontawesome-free/css/fontawesome.min.css' => [
        'version' => '6.6.0',
        'type' => 'css',
    ],
    '@fortawesome/fontawesome-free/css/all.min.css' => [
        'version' => '6.6.0',
        'type' => 'css',
    ],
];
