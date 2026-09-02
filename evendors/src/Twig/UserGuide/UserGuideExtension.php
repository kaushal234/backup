<?php

declare(strict_types=1);

namespace App\Twig\UserGuide;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UserGuideExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_user_guide', $this->getUserGuide(...)),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getUserGuide(string $pathInfo): array
    {
        return UserGuide::getMapping($pathInfo);
    }
}
