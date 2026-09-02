<?php

declare(strict_types=1);

namespace App\FileSystem;

interface AdapterContextProviderInterface
{
    public static function getClass(): string;
}
