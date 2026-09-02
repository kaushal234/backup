<?php

declare(strict_types=1);

namespace App\Tests\AI\Handler;

use App\AI\Dto\Result;
use App\AI\Handler\LegacySearchSourceHandler;
use LegacyBundle\Manager\CommonManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class LegacySearchSourceHandlerTest extends TestCase
{
    public function testSupportsReturnsTrueForConfiguredModule(): void
    {
        self::assertTrue($this->makeHandler(configuration: $this->makeConfiguration())->supports('contact'));
    }

    public function testSupportsReturnsFalseForMissingModule(): void
    {
        self::assertFalse($this->makeHandler(configuration: $this->makeConfiguration())->supports('company'));
    }

    public function testHandleReturnsNullWhenSourceIsEmpty(): void
    {
        $manager = $this->createMock(CommonManager::class);
        $manager->expects(self::once())
            ->method('getSource')
            ->with('contact', 42, ['firstname', 'lastname'])
            ->willReturn([]);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::never())->method('generate');

        $result = $this->makeHandler($manager, $urlGenerator, $this->makeConfiguration())
            ->handle(['src' => 'CONTACT', 'id' => 42]);

        self::assertNull($result);
    }

    public function testHandleBuildsDescriptionAndGeneratesLinkWithRouteParameter(): void
    {
        $manager = $this->createMock(CommonManager::class);
        $manager->method('getSource')
            ->with('contact', 42, ['firstname', 'lastname'])
            ->willReturn(['firstname' => 'John', 'lastname' => 'Doe']);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('legacy_route', ['m' => ['contact', 'view'], 'id' => 42])
            ->willReturn('/legacy/contact/42');

        $result = $this->makeHandler($manager, $urlGenerator, $this->makeConfiguration(routeWithNoParams: false))
            ->handle(['src' => 'CONTACT', 'id' => 42]);

        self::assertInstanceOf(Result::class, $result);
        self::assertSame(42, $result->id);
        self::assertSame('CONTACT', $result->module);
        self::assertSame('John Doe', $result->description);
        self::assertSame('/legacy/contact/42', $result->link);
    }

    public function testHandleBuildsDescriptionAndGeneratesLinkWithoutRouteParameter(): void
    {
        $manager = $this->createMock(CommonManager::class);
        $manager->method('getSource')->willReturn(['firstname' => 'John', 'lastname' => 'Doe']);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('legacy_route', ['m' => ['view'], 'id' => 42])
            ->willReturn('/legacy/42');

        $result = $this->makeHandler($manager, $urlGenerator, $this->makeConfiguration(routeWithNoParams: true))
            ->handle(['src' => 'CONTACT', 'id' => 42]);

        self::assertInstanceOf(Result::class, $result);
        self::assertSame('/legacy/42', $result->link);
    }

    public function testHandleBuildsDescriptionWithMissingFieldsAsEmptyStrings(): void
    {
        $manager = $this->createMock(CommonManager::class);
        $manager->method('getSource')
            ->with('contact', 42, ['firstname', 'lastname', 'email'])
            ->willReturn(['firstname' => 'John', 'lastname' => 'Doe']);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/legacy/contact/42');

        $result = $this->makeHandler(
            $manager,
            $urlGenerator,
            $this->makeConfiguration(fields: ['firstname', 'lastname', 'email']),
        )->handle(['src' => 'CONTACT', 'id' => 42]);

        self::assertInstanceOf(Result::class, $result);
        self::assertSame('John Doe ', $result->description);
    }

    /**
     * @param array<string, array{fields: list<string>, table: string, route: string, route_parameter: string, route_with_no_params: bool}> $configuration
     */
    private function makeHandler(
        ?CommonManager $manager = null,
        ?UrlGeneratorInterface $urlGenerator = null,
        array $configuration = [],
    ): LegacySearchSourceHandler {
        return new LegacySearchSourceHandler(
            $manager ?? $this->createMock(CommonManager::class),
            $urlGenerator ?? $this->createMock(UrlGeneratorInterface::class),
            $configuration,
        );
    }

    /**
     * @param list<string> $fields
     *
     * @return array<string, array{fields: list<string>, table: string, route: string, route_parameter: string, route_with_no_params: bool}>
     */
    private function makeConfiguration(
        array $fields = ['firstname', 'lastname'],
        bool $routeWithNoParams = false,
    ): array {
        return [
            'contact' => [
                'fields' => $fields,
                'table' => 'contact',
                'route' => 'legacy_route',
                'route_parameter' => 'contact',
                'route_with_no_params' => $routeWithNoParams,
            ],
        ];
    }
}
