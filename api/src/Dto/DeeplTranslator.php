<?php

declare(strict_types=1);

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\DeeplTranslatorController;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/translate',
            controller: DeeplTranslatorController::class,
            name: 'translate',
        ),
    ],
    routePrefix: 'deepl',
    denormalizationContext: ['groups' => ['deepl']]
)]
class DeeplTranslator
{
    public const string DEFAULT_LANGUAGE_CODE = 'EN';
    public const array SUPPORTED_LANGUAGE_CODES = [
        'AR', 'BG', 'CS', 'DA', 'DE', 'EL', 'EN', 'ES', 'ET',
        'FI', 'FR', 'HU', 'ID', 'IT', 'JA', 'KO', 'LT', 'LV',
        'NB', 'NL', 'PL', 'PT', 'RO', 'RU', 'SK', 'SL', 'SV',
        'TR', 'UK', 'ZH-HANT', 'ZH-HANS',
    ];

    public const string DEFAULT_FORMALITY = 'default';

    public const array LEVEL_FORMALITIES = [
        'default', 'prefer_more', 'prefer_less',
    ];

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['deepl'])]
    public string $message;

    #[Groups(['deepl'])]
    public ?string $module = null;

    #[Groups(['deepl'])]
    public ?int $moduleId = null;

    #[Groups(['deepl'])]
    #[Assert\Choice(choices: self::SUPPORTED_LANGUAGE_CODES)]
    public ?string $targetLang = self::DEFAULT_LANGUAGE_CODE;

    #[Groups(['deepl'])]
    #[Assert\Choice(choices: self::LEVEL_FORMALITIES)]
    public ?string $formality = self::DEFAULT_FORMALITY;
}
