<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'manufacturing:webcams:france')]
class WebcamsFrCommand extends AbstractWebcamsCommand
{
    public function getDescription(): string
    {
        return 'Get webcams pictures from France';
    }

    protected function getConfig(): array
    {
        return [
            [
                'bu' => 'MTL',
                'cam' => 'SHOP B1',
                'url' => 'http://10.11.174.5/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'MTL_SHOP_B1.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'tired',
            ],
            [
                'bu' => 'MTL',
                'cam' => 'SHOP B2',
                'url' => 'http://10.11.174.6/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'MTL_SHOP_B2.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => ']9Y6xD\+<{[#txu{E9',
            ],
            [
                'bu' => 'STL',
                'cam' => 'SHOP ABS',
                'url' => 'http://CMA000008.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'STL_SHOP_ABS.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => ']9Y6xD\+<{[#txu{E9',
            ],
            [
                'bu' => 'STL',
                'cam' => 'SHOP NBL',
                'url' => 'http://CMA000004.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'STL_SHOP_NBL.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => ']9Y6xD\+<{[#txu{E9',
            ],
            [
                'bu' => 'STL',
                'cam' => 'SHOP NBL 2',
                'url' => 'http://CMA000007.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'STL_SHOP_NBL_2.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'NgoM85VnbO99ML',
            ],
            [
                'bu' => 'STL',
                'cam' => 'SHOP JET-16',
                'url' => 'http://CMA000006.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'STL_SHOP_JET-16.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => ']9Y6xD\+<{[#txu{E9',
            ],
            [
                'bu' => 'STL',
                'cam' => 'SHOP WHSE',
                'url' => 'http://CMA000009.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'STL_SHOP_WHSE.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => ']9Y6xD\+<{[#txu{E9',
            ],
            [
                'bu' => 'SOR',
                'cam' => 'CAM 1',
                'url' => 'http://www:tired@10.11.14.3/jpg/1/image.jpg',
                'dest' => 'SOR_01.jpg',
                'curl' => false,
            ],
            [
                'bu' => 'SOR',
                'cam' => 'CAM 2',
                'url' => 'http://www:tired@10.11.14.4/jpg/1/image.jpg',
                'dest' => 'SOR_02.jpg',
                'curl' => false,
            ],
            [
                'bu' => 'SOR',
                'cam' => 'CAM 3',
                'url' => 'http://www:tired@10.11.14.5/jpg/1/image.jpg',
                'dest' => 'SOR_03.jpg',
                'curl' => false,
            ],
            [
                'bu' => 'SOR',
                'cam' => 'CAM 4',
                'url' => 'http://www:tired@10.11.14.6/jpg/1/image.jpg',
                'dest' => 'SOR_04.jpg',
                'curl' => false,
            ],
            [
                'bu' => 'SOR',
                'cam' => 'CAM 5',
                'url' => 'http://CMA000025.tld-europe.local/jpg/1/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'SOR_05.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => ']9Y6xD\+<{[#txu{E9',
            ],
        ];
    }
}
