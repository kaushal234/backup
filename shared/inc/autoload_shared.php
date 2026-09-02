<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    $classFolder = (explode('\\', $class))[0];

    if ('Shared' !== $classFolder) {
        return;
    }

    $class_path = str_replace(['\\', 'Shared/'], ['/', ''], $class);
    $file = __DIR__.'/API/'.$class_path.'.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
