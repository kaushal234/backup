<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'manufacturing:webcams:belgium')]
class WebcamsBeCommand extends AbstractWebcamsCommand
{
    public function getDescription(): string
    {
        return 'Get webcams pictures from LEB';
    }

    protected function getConfig(): array
    {
        return [
            [
                'bu' => 'LEB',
                'cam' => 'ATELIER 1',
                'url' => 'http://10.11.110.10/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'LEB_ATELIER_1.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'qfqHkxzuBSYNx4vw',
            ],
            [
                'bu' => 'LEB',
                'cam' => 'ATELIER 2',
                'url' => 'http://10.11.110.11/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'LEB_ATELIER_2.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'qfqHkxzuBSYNx4vw',
            ],
        ];
    }
}
