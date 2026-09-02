<?php

declare(strict_types=1);

namespace App\FileSystem;

interface AdapterInterface
{
    public function getContextProvider(): AdapterContextProviderInterface;
}
