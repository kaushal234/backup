<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class DynamicForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public string $formClass;

    #[LiveProp]
    public ?array $initialFormData = [];

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm($this->formClass, $this->initialFormData);
    }
}
