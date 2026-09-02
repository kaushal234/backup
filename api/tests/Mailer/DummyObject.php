<?php

declare(strict_types=1);

namespace App\Tests\Mailer;

final class DummyObject
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function is($name)
    {
        return $this->name === $name;
    }
}
