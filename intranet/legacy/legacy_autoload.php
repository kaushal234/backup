<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    $classFolder = (explode('\\', $class))[0];

    if ('Legacy' !== $classFolder) {
        return;
    }

    $class_path = str_replace(['\\', 'Legacy/'], ['/', ''], $class);
    $file = __DIR__.'/src/'.$class_path.'.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
