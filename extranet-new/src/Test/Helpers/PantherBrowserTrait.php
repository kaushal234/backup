<?php

declare(strict_types=1);

namespace App\Test\Helpers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\Client as PantherClient;

trait PantherBrowserTrait
{
    /**
     * Shortcut to log a user in a panther client.
     */
    public static function loginUser(string $identifier = 'julien.lepers@tld.com', string $password = 'P@ssw0rd15chars'): Crawler
    {
        $client = self::createPantherClient();
        $crawler = $client->request(Request::METHOD_GET, '/security/login');

        $form = $crawler->selectButton('Continue')->form(['_identifier' => $identifier, '_password' => $password], Request::METHOD_POST);

        $crawler = $client->submit($form);

        $client->getCookieJar()->set(new Cookie('_locale', 'en', null, '/'));

        return $crawler;
    }

    public static function assertPathEquals(string $expectedPath): void
    {
        $client = self::createPantherClient();

        $urlInfo = (array) parse_url($client->getCurrentURL());
        $path = $urlInfo['path'] ?? null;

        TestCase::assertNotNull($path);
        TestCase::assertEquals($expectedPath, $path);
    }

    abstract protected static function createPantherClient(array $options = [], array $kernelOptions = [], array $managerOptions = []): PantherClient;

    abstract protected static function getContainer(): ContainerInterface;
}
