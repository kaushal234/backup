<?php

declare(strict_types=1);

namespace AppBundle\Subscription;

use AppBundle\Manager\SettingsManager;
use AppBundle\Registry\SubscriptionRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Forms;
use Symfony\Component\Routing\RouterInterface;

class SubscriptionFormFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testScalarFieldValuesAreKeptAsIs(): void
    {
        $form = $this->createFactory(
            settings: ['indiceFactor' => ['IF 1', 'IF 10']],
            scalarFields: ['indiceFactor'],
        )->create('test.key');

        self::assertSame(
            ['indiceFactor' => ['IF 1', 'IF 10']],
            $form->getNormData(),
        );
    }

    public function testIriFieldValuesAreWrappedWithAtId(): void
    {
        $form = $this->createFactory(
            settings: ['product' => ['/api/products/1', '/api/products/2']],
            scalarFields: [],
        )->create('test.key');

        self::assertSame(
            ['product' => [['@id' => '/api/products/1'], ['@id' => '/api/products/2']]],
            $form->getNormData(),
        );
    }

    public function testMixedScalarAndIriFields(): void
    {
        $form = $this->createFactory(
            settings: [
                'indiceFactor' => ['IF 1'],
                'product' => ['/api/products/1'],
            ],
            scalarFields: ['indiceFactor'],
        )->create('test.key');

        self::assertSame(
            [
                'indiceFactor' => ['IF 1'],
                'product' => [['@id' => '/api/products/1']],
            ],
            $form->getNormData(),
        );
    }

    public function testEmptySettingsProduceEmptyParameters(): void
    {
        $form = $this->createFactory(
            settings: [],
            scalarFields: [],
        )->create('test.key');

        self::assertSame([], $form->getNormData());
    }

    public function testNullSettingsProduceEmptyParameters(): void
    {
        $form = $this->createFactory(
            settings: null,
            scalarFields: [],
        )->create('test.key');

        self::assertSame([], $form->getNormData());
    }

    private function createFactory(?array $settings, array $scalarFields): SubscriptionFormFactory
    {
        $subscription = $this->prophesize(SubscriptionInterface::class);
        $subscription->getFormType()->willReturn(FormType::class);
        $subscription->getScalarFields()->willReturn($scalarFields);

        $registry = $this->prophesize(SubscriptionRegistry::class);
        $registry->get('test.key')->willReturn($subscription->reveal());

        $settingsManager = $this->prophesize(SettingsManager::class);
        $settingsManager->get('test.key')->willReturn($settings);

        $router = $this->prophesize(RouterInterface::class);
        $router->generate('subscriptions', ['settingKey' => 'test.key'])->willReturn('/subscriptions/test.key');

        return new SubscriptionFormFactory(
            $registry->reveal(),
            $settingsManager->reveal(),
            Forms::createFormFactory(),
            $router->reveal(),
        );
    }
}
