<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\ResourceSourceProvider\PurchaseOrderResourceSourceProvider;
use App\Security\Security;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group unit
 */
final class PurchaseOrderResourceSourceProviderTest extends TestCase
{
    public function testGetFindSourceWithFrLocale(): void
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $security = new Security($authorizationChecker, $tokenStorage);
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())->method('getLocale')->willReturn('fr');
        $provider = new PurchaseOrderResourceSourceProvider($security, $translator);

        $httpSource = $provider->getFindSource('1');
        self::assertSame('fr', $httpSource->options['query']['otherLanguage']);
    }

    public function testGetFindSourceDefaultLanguage(): void
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $security = new Security($authorizationChecker, $tokenStorage);
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())->method('getLocale')->willReturn('en');
        $provider = new PurchaseOrderResourceSourceProvider($security, $translator);

        $httpSource = $provider->getFindSource('1');
        self::assertNull($httpSource->options['query']['otherLanguage']);
    }
}
