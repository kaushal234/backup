<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SwitchType extends AbstractType
{
    public function getParent(): string
    {
        return CheckboxType::class;
    }
}
