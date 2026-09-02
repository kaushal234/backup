<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\LocaleSubscriber;
use App\Locale;
use App\Security\User\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psl\Vec;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Translation\LocaleSwitcher;

/**
 * @group unit
 */
final class LocaleSubscriberTest extends TestCase
{
    private LocaleSwitcher&MockObject $switcher;
    private KernelInterface&MockObject $kernel;
    private Request $request;
    private LocaleSubscriber $subscriber;
    private RequestEvent $event;

    protected function setUp(): void
    {
        $this->switcher = $this->createMock(LocaleSwitcher::class);
        $this->kernel = $this->createMock(KernelInterface::class);
        $this->request = Request::create('/');
        $this->event = new RequestEvent($this->kernel, $this->request, HttpKernelInterface::MAIN_REQUEST);
        $this->subscriber = new LocaleSubscriber($this->switcher);
    }

    public function testOnKernelRequest(): void
    {
        $this->switcher->expects($this->never())->method('setLocale');

        $this->subscriber->onKernelRequest($this->event);
    }

    public function testOnKernelRequestSetsLocale(): void
    {
        $this->switcher
            ->expects($this->exactly(3))
            ->method('setLocale')
            ->withConsecutive(...Vec\map(Locale::cases(), static fn (Locale $locale): array => [$locale->value]))
        ;

        foreach (Locale::cases() as $locale) {
            $this->request->cookies->set('_locale', $locale->value);

            $this->subscriber->onKernelRequest($this->event);
        }
    }

    public function testOnKernelRequestDoesntSetInvalidLocale(): void
    {
        $this->switcher->expects($this->never())->method('setLocale');

        $this->request->cookies->set('_locale', 'foo');

        $this->subscriber->onKernelRequest($this->event);
    }

    /**
     * @dataProvider languagesProvider
     *
     * @throws \ReflectionException
     */
    public function testOnLoginSuccessSetLocaleCookies(?string $apiLanguage, string $expectedLocaleCookie): void
    {
        $response = new Response();
        $event = $this->createMock(LoginSuccessEvent::class);
        $user = (new \ReflectionClass(User::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(User::class, 'language'))->setValue($user, $apiLanguage);
        $event->method('getUser')->willReturn($user);
        $event->method('getResponse')->willReturn($response);

        $this->subscriber->onLoginSuccessSetLocaleCookies($event);

        $cookies = $response->headers->getCookies();
        self::assertCount(1, $cookies);
        self::assertSame('_locale', $cookies[0]->getName());
        self::assertSame($expectedLocaleCookie, $cookies[0]->getValue());
    }

    /**
     * @return iterable<string, array{?string, string}>
     */
    public static function languagesProvider(): iterable
    {
        yield 'fr stay fr' => ['fr', 'fr'];
        yield 'en stay en' => ['en', 'en'];
        yield 'zh become zh-CN' => ['zh', 'zh-CN'];
        yield 'null switch to en' => [null, 'en'];
        yield 'unknown switch to en' => ['de', 'en'];
    }
}
