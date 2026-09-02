<?php

declare(strict_types=1);

namespace App\Formatter\Snappy;

class Adapter
{
    private ?string $headerTemplate = null;

    private ?string $footerTemplate = null;

    private readonly string $template;

    private array $context;

    private array $options = [];

    /**
     * Adapter constructor.
     */
    public function __construct(string $template, array $context)
    {
        $this->template = $template;
        $this->context = $context;
    }

    public function getHeaderTemplate(): ?string
    {
        return $this->headerTemplate;
    }

    public function setHeaderTemplate(?string $headerTemplate)
    {
        $this->headerTemplate = $headerTemplate;
    }

    public function getFooterTemplate(): ?string
    {
        return $this->footerTemplate;
    }

    public function setFooterTemplate(?string $footerTemplate)
    {
        $this->footerTemplate = $footerTemplate;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function addToContext(string $key, $value): self
    {
        $this->context[$key] = $value;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function addOption(string $key, $value): self
    {
        $this->options[$key] = $value;

        return $this;
    }
}
