<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\LocaleSubscriber;
use App\Locale;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psl\Vec;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
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
}
