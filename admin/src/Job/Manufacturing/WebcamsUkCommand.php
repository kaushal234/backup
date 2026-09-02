<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'manufacturing:webcams:uk')]
class WebcamsUkCommand extends AbstractWebcamsCommand
{
    public function getDescription(): string
    {
        return 'Get webcams pictures from KEM';
    }

    protected function getConfig(): array
    {
        return [
            [
                'bu' => 'KEM',
                'cam' => 'ATELIER 1',
                'url' => 'http://10.11.142.8/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'KEM_ATELIER_1.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'PV04cm135975?',
            ],
            [
                'bu' => 'KEM',
                'cam' => 'ATELIER 2',
                'url' => 'http://10.11.142.9/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'KEM_ATELIER_2.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'PV04cm135975?',
            ],
            [
                'bu' => 'KEM',
                'cam' => 'ATELIER 3',
                'url' => 'http://10.11.142.10/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'KEM_ATELIER_3.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'PV04cm135975?',
            ],
        ];
    }
}
