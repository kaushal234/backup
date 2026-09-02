<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Normalizer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Common\Subscription;
use App\Entity\Module\Module;
use App\Manager\EntityDictionaryManager;
use App\Serializer\Normalizer\SubscriptionNormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SubscriptionNormalizerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $entityDictionaryManager;
    private ObjectProphecy $iriConverter;
    private ObjectProphecy $decorated;
    private SubscriptionNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->entityDictionaryManager = $this->prophesize(EntityDictionaryManager::class);
        $this->iriConverter = $this->prophesize(IriConverterInterface::class);
        $this->decorated = $this->prophesize(NormalizerInterface::class);
        $this->normalizer = new SubscriptionNormalizer(
            $this->entityDictionaryManager->reveal(),
            $this->iriConverter->reveal(),
            [],
        );
        $this->normalizer->setNormalizer($this->decorated->reveal());
    }

    public function testGetSupportedTypes(): void
    {
        self::assertSame(['*' => false], $this->normalizer->getSupportedTypes(null));
    }

    public function testSupportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization($this->createSubscription('/sales/demos/1')));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
        self::assertFalse($this->normalizer->supportsNormalization(
            $this->createSubscription('/sales/demos/1'),
            null,
            ['SUBSCRIPTION_NORMALIZER_ALREADY_CALLED' => true]
        ));
    }

    public function testNormalizeAddsResourceIdAndModule(): void
    {
        $subscription = $this->createSubscription('/sales/demos/123');

        $this->decorated
            ->normalize($subscription, null, Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn(['@id' => '/api/subscriptions/42', 'resource' => '/sales/demos/123'])
        ;

        $this->iriConverter
            ->getResourceFromIri('/sales/demos/123')
            ->shouldBeCalledOnce()
            ->willReturn(new \stdClass())
        ;

        $module = new Module();
        $module->setName('DEMO');
        $module->frontEndRoute = 'sales_demos_show';

        $this->entityDictionaryManager
            ->getIndexedTable(Module::class, ['name'])
            ->shouldBeCalledOnce()
            ->willReturn(['DEMO' => $module])
        ;

        $result = $this->normalizer->normalize($subscription);

        self::assertSame(123, $result['resourceId']);
        self::assertSame(['name' => 'DEMO', 'frontEndRoute' => 'sales_demos_show'], $result['module']);
    }

    public function testNormalizeReturnsNullModuleWhenIriDoesNotMatchAnyEnum(): void
    {
        $subscription = $this->createSubscription('/unknown/path/7');

        $this->decorated
            ->normalize($subscription, null, Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn(['resource' => '/unknown/path/7'])
        ;

        $this->iriConverter
            ->getResourceFromIri('/unknown/path/7')
            ->shouldBeCalledOnce()
            ->willReturn(new \stdClass())
        ;

        $this->entityDictionaryManager
            ->getIndexedTable(Argument::any(), Argument::any())
            ->shouldNotBeCalled()
        ;

        $result = $this->normalizer->normalize($subscription);

        self::assertSame(7, $result['resourceId']);
        self::assertNull($result['module']);
    }

    public function testNormalizeReturnsNullModuleWhenModuleNotInDictionary(): void
    {
        $subscription = $this->createSubscription('/sales/demos/9');

        $this->decorated
            ->normalize($subscription, null, Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn(['resource' => '/sales/demos/9'])
        ;

        $this->iriConverter
            ->getResourceFromIri('/sales/demos/9')
            ->shouldBeCalledOnce()
            ->willReturn(new \stdClass())
        ;

        $this->entityDictionaryManager
            ->getIndexedTable(Module::class, ['name'])
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;

        $result = $this->normalizer->normalize($subscription);

        self::assertSame(9, $result['resourceId']);
        self::assertNull($result['module']);
    }

    private function createSubscription(string $iri): Subscription
    {
        $subscription = new Subscription();
        $subscription->setResource($iri);

        return $subscription;
    }
}
