<?php

declare(strict_types=1);

namespace App\Postman;

use App\Postman\Resource\Node;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Finder
{
    public function find(Collection $nodes, array $paths): Node
    {
        $key = reset($paths);

        $filtered = $nodes->filter(static function ($value) use ($key) {
            return $value->name === $key;
        });

        $currentNode = $filtered->first();

        if (false === $currentNode) {
            $currentNode = new Node();
            $currentNode->name = $key;

            $nodes->add($currentNode);
        }

        if (next($paths)) {
            // Remove current value on the path to access next level
            reset($paths);
            $currentKey = key($paths);
            unset($paths[$currentKey]);

            if (null === $currentNode->item) {
                $currentNode->item = new ArrayCollection();
            }

            return $this->find($currentNode->item, $paths);
        }

        return $currentNode;
    }
}
