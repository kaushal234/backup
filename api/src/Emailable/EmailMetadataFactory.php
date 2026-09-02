<?php

declare(strict_types=1);

namespace App\Emailable;

class EmailMetadataFactory
{
    private array $adapters = [];

    /**
     * @return EmailMetadataInterface
     */
    public function getAdapter($resource)
    {
        $adapter = null;

        foreach ($this->adapters as $adapter) {
            if ($adapter::supports($resource)) {
                break;
            }
            $adapter = null;
        }

        if (null === $adapter) {
            throw new \InvalidArgumentException(\sprintf('No adapter found for resource %s', $resource::class));
        }

        return new $adapter($resource);
    }

    public function registerAdapter($adapterClassName)
    {
        if (!is_a($adapterClassName, EmailMetadataInterface::class, true)) {
            throw new \InvalidArgumentException(\sprintf('Class %s is not an EmailMetadataInterface.', $adapterClassName));
        }

        $this->adapters[] = $adapterClassName;
    }
}
