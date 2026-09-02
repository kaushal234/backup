<?php

declare(strict_types=1);

namespace App\Tests\Twig\Emails;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

/**
 * The "new comment" TOC email must show the English (DeepL) translation first,
 * with the original-language text in a muted block below it. When no translation
 * is available, only the original text is rendered with no empty block.
 */
class TocCommentMessagePartialTest extends KernelTestCase
{
    private const TEMPLATE = 'Emails/Service/TechnicianOnCall/_partials/_comment_message.html.twig';

    private Environment $twig;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->twig = self::getContainer()->get(Environment::class);
    }

    public function testTranslationIsRenderedBeforeTheOriginalText(): void
    {
        $html = $this->twig->render(self::TEMPLATE, [
            'message' => 'Bonjour, la pièce a été expédiée.',
            'translation' => 'Hello, the part has been shipped.',
        ]);

        $translationPos = mb_strpos($html, 'Hello, the part has been shipped.');
        $originalPos = mb_strpos($html, 'Bonjour, la pièce a été expédiée.');

        self::assertNotFalse($translationPos);
        self::assertNotFalse($originalPos);
        self::assertLessThan($originalPos, $translationPos, 'The translation must appear before the original text.');
        self::assertStringContainsString('Original text:', $html);
    }

    public function testOnlyOriginalTextIsRenderedWhenNoTranslationIsAvailable(): void
    {
        $html = $this->twig->render(self::TEMPLATE, [
            'message' => 'The part has been shipped.',
            'translation' => null,
        ]);

        self::assertStringContainsString('The part has been shipped.', $html);
        self::assertStringNotContainsString('Original text:', $html);
    }

    public function testEmptyTranslationIsTreatedAsNoTranslation(): void
    {
        $html = $this->twig->render(self::TEMPLATE, [
            'message' => 'The part has been shipped.',
            'translation' => '',
        ]);

        self::assertStringContainsString('The part has been shipped.', $html);
        self::assertStringNotContainsString('Original text:', $html);
    }
}
