<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait KernelAwareTrait
{
    private KernelInterface $kernel;

    #[Required]
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }
}
