<?php

declare(strict_types=1);

namespace ApiBundle\Hydra;

class HydraMetadataBag
{
    private array $metadata;

    public function __construct(array $data)
    {
        $this->metadata = array_filter($data, static fn ($key) => '@' === $key[0] || (0 === mb_strpos((string) $key, 'hydra:') && 'hydra:member' !== $key), \ARRAY_FILTER_USE_KEY);
    }

    /**
     * Check the existence of a specific metadata.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->metadata['hydra:'.$name]);
    }

    /**
     * Return a metadata.
     *
     * @param string $name
     * @param null   $default
     *
     * @return mixed|null
     */
    public function get($name, $default = null)
    {
        if ($this->has($name)) {
            return $this->metadata['hydra:'.$name];
        }

        return $default;
    }
}
