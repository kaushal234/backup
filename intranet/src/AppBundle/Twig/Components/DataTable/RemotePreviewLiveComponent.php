<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\DataTable;

use ApiBundle\Client;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('RemotePreview')]
class RemotePreviewLiveComponent
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $icon = 'tabler:info-circle';

    #[LiveProp]
    public array $iconAttr = [];

    #[LiveProp]
    public string $resource = '';

    #[LiveProp]
    public array $query = [];

    #[LiveProp]
    public array $order = [];

    #[LiveProp]
    public string $template = '';

    #[LiveProp]
    public array $templateVars = [];

    #[LiveProp]
    public ?string $title = null;

    #[LiveProp]
    public ?string $href = null;

    /**
     * Fetched lazily on hover, then kept as-is: it round-trips through the live
     * component's serialized state, so it survives between AJAX calls without
     * being re-fetched on every subsequent hover of the same cell.
     */
    #[LiveProp]
    public ?array $data = null;

    public function __construct(
        private readonly Client $client,
    ) {
    }

    #[LiveAction]
    public function load(): void
    {
        if (null !== $this->data) {
            return;
        }

        $this->data = $this->client
            ->findBy($this->resource, $this->query, $this->order)
            ->getSimpleArrayCopy();
    }
}
