<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord\Survey;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.survey.builder')]
interface BuilderInterface
{
    public function supports(array $data): bool;
}
