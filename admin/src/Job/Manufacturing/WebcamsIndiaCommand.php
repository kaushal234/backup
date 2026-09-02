<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'manufacturing:webcams:india')]
class WebcamsIndiaCommand extends AbstractWebcamsCommand
{
    public function getDescription(): string
    {
        return 'Get webcams pictures from MAINI';
    }

    protected function getConfig(): array
    {
        return [
            [
                'bu' => 'MAI',
                'cam' => 'SHOP 1',
                'url' => 'http://CMA000011.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'MAI_SHOP_01.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => ']9Y6xD\+<{[#txu{E9',
            ],
            [
                'bu' => 'MAI',
                'cam' => 'SHOP 2',
                'url' => 'http://CMA000012.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'MAI_SHOP_02.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => ']9Y6xD\+<{[#txu{E9',
            ],
            [
                'bu' => 'MAI',
                'cam' => 'SHOP 3',
                'url' => 'http://CMA000013.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'MAI_SHOP_03.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => ']9Y6xD\+<{[#txu{E9',
            ],
            [
                'bu' => 'MAI',
                'cam' => 'SHOP 4',
                'url' => 'http://CMA000028.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'MAI_SHOP_04.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'CV}_)WJ\`y2(,f+i9G',
            ],
            [
                'bu' => 'MAI',
                'cam' => 'SHOP 5',
                'url' => 'http://CMA000029.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'MAI_SHOP_05.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'CV}_)WJ\`y2(,f+i9G',
            ],
            [
                'bu' => 'MAI',
                'cam' => 'SHOP 6',
                'url' => 'http://CMA000030.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'MAI_SHOP_06.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'CV}_)WJ\`y2(,f+i9G',
            ],
            [
                'bu' => 'MAI',
                'cam' => 'SHOP 7',
                'url' => 'http://CMA000031.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'MAI_SHOP_07.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'CV}_)WJ\`y2(,f+i9G',
            ],
            [
                'bu' => 'MAI',
                'cam' => 'SHOP 8',
                'url' => 'http://CMA000032.tld-europe.local/jpg/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'MAI_SHOP_08.jpg',
                'curl' => true,
                'username' => 'root',
                'password' => 'CV}_)WJ\`y2(,f+i9G',
            ],
        ];
    }
}
