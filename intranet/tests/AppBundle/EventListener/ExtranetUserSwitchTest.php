<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\EventListener;

use ApiBundle\Client;
use ApiBundle\Security\Core\Authentication\JwtCookieFactory;
use AppBundle\EventListener\ImpersonateUserListener;
use AppBundle\Twig\Extension\ExternalUrlExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExtranetUserSwitchTest extends TestCase
{
    /**
     * @dataProvider impersonateDataProvider
     */
    public function testHandleUserSwitch($impersonateUrl, $userIdentifier, $baseExtranetUrl, $expectedTargetUrl)
    {
        $clientMock = $this->createMock(Client::class);
        $translatorMock = $this->createMock(TranslatorInterface::class);
        $urlExtensionMock = $this->createMock(ExternalUrlExtension::class);
        $cookieFactoryMock = $this->createMock(JwtCookieFactory::class);
        $cookie = new Cookie('_jwt', 'toto');

        $clientMock->expects($this->once())
            ->method('post')
            ->willReturn(['token' => 'mocked_token']);

        $translatorMock->expects($this->exactly(0))
            ->method('trans');

        $urlExtensionMock->expects($this->once())
            ->method('getFullUrl')
            ->with('portal_extranet_impersonate')
            ->willReturn($baseExtranetUrl);

        $cookieFactoryMock->expects($this->once())
            ->method('setDomainFromRequestHttpHost')
            ->with('www.tld-gse.com');

        $cookieFactoryMock->expects($this->once())
            ->method('generate')
            ->with('mocked_token')
            ->willReturn($cookie);

        $listener = new ImpersonateUserListener($clientMock, $translatorMock, $urlExtensionMock, $cookieFactoryMock);
        $request = Request::create('https://www.tld-gse.com/?_impersonate='.$impersonateUrl.'&_userIdentifier='.$userIdentifier);
        $event = new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);

        $listener->handleUserSwitch($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame($expectedTargetUrl, $response->getTargetUrl());
        $this->assertSame('toto', $response->headers->getCookies()[0]->getValue());
    }

    public function impersonateDataProvider(): array
    {
        return [
            ['/sales/extranet_users/299', 'contact@tld-gse.com', 'https://portal_extranet.tld-gse.com', 'https://portal_extranet.tld-gse.com?_userIdentifier=contact@tld-gse.com'],
        ];
    }
}
