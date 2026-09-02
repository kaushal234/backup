<?php

if (file_exists(dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php')) {
    require dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php';
}

if (file_exists(dirname(__DIR__).'/vendor/azjezz/psl/src/preload.php')) {
    require dirname(__DIR__).'/vendor/azjezz/psl/src/preload.php';
}
