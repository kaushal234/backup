<?php

declare(strict_types=1);

namespace Tests\AppBundle\Twig\Components\DataTable;

use AppBundle\Twig\Components\DataTable\RemotePreviewLiveComponent;
use Tests\AppBundle\Twig\Components\LiveComponentTestCase;

class RemotePreviewLiveComponentTest extends LiveComponentTestCase
{
    public function testLoadFetchesDataFromTheConfiguredResource(): void
    {
        $this->login('superuser');

        $this->mockApi('comments', ['hydra:member' => [
            ['@id' => '/comments/1', 'message' => 'Do it or do not. there is no try'],
            ['@id' => '/comments/2', 'message' => 'The greatest teacher, failure is.'],
        ]]);

        $component = $this->createLiveComponent(RemotePreviewLiveComponent::class, [
            'icon' => 'tabler:messages',
            'resource' => 'comments',
            'query' => ['resource' => '/service/technician_on_calls/42'],
            'order' => ['createdAt' => 'desc'],
        ])->component();

        $component->load();

        self::assertSame([
            ['@id' => '/comments/1', 'message' => 'Do it or do not. there is no try'],
            ['@id' => '/comments/2', 'message' => 'The greatest teacher, failure is.'],
        ], $component->data);
    }

    public function testLoadDoesNothingWhenDataIsAlreadyLoaded(): void
    {
        $this->login('superuser');

        $component = $this->createLiveComponent(RemotePreviewLiveComponent::class, [
            'icon' => 'tabler:messages',
            'resource' => 'comments',
        ])->component();
        $component->data = ['already' => 'loaded'];

        $component->load();

        self::assertSame(['already' => 'loaded'], $component->data);
    }

    public function testDataStartsAsNullUntilLoaded(): void
    {
        $this->login('superuser');

        $component = $this->createLiveComponent(RemotePreviewLiveComponent::class, [
            'icon' => 'tabler:messages',
            'resource' => 'comments',
        ])->component();

        self::assertNull($component->data);
    }
}
