<?php

declare(strict_types=1);

namespace LegacyBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Sequence extends Task
{
    public array $closeParams = [];

    #[Assert\NotBlank]
    protected string $module = 'SEQ';

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['TEMPLATE', 'SINGLE_LEVEL', 'USER_LEVEL'])]
    protected string $mode = 'TEMPLATE';

    #[Assert\NotNull]
    protected string $templateName;

    protected string $templateDescription = '';

    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @return $this
     */
    public function setMode(string $mode)
    {
        $this->mode = $mode;

        return $this;
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * @return $this
     */
    public function setTemplateName(string $templateName)
    {
        $this->templateName = $templateName;

        return $this;
    }

    public function getTemplateDescription(): string
    {
        return $this->templateDescription;
    }

    /**
     * @return $this
     */
    public function setTemplateDescription(string $templateDescription)
    {
        $this->templateDescription = $templateDescription;

        return $this;
    }

    public function getCloseParams(): array
    {
        return $this->closeParams;
    }

    /**
     * @return $this
     */
    public function setCloseParams(array $closeParams): self
    {
        $this->closeParams = $closeParams;

        return $this;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): self
    {
        $this->module = $module;

        return $this;
    }
}
