<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\Http\ResourceSourceProvider;

use App\Sdk\Http\ResourceSourceProvider\DocumentResourceSourceProvider;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
final class DocumentResourceSourceProviderTest extends TestCase
{
    public function testGetFindAllSource(): void
    {
        $provider = new DocumentResourceSourceProvider();
        $httpSource = $provider->getFindAllSource();

        self::assertSame(['ACTIVE', 'EXPIRED', 'REVISION', 'APPROVAL'], $httpSource->options['query']['status']);
    }
}
